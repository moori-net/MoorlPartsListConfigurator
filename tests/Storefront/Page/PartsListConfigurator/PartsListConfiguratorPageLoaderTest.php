<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Tests\Storefront\Page\PartsListConfigurator;

use Moorl\PartsListConfigurator\Core\Calculator\PartsListCalculatorInterface;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPage;
use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPageLoader;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use MoorlFoundation\Core\Content\PartsList\PartsListEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Storefront\Page\MetaInformation;

class PartsListConfiguratorPageLoaderTest extends TestCase
{
    #[DataProvider('axisProvider')]
    public function testCustomFieldValueIsAssignedToMatchingAxis(
        string $axis,
        string $customField,
        string $calculatorMethod,
        string $getter
    ): void {
        $groupId = '01990bc70f3a7c13a09f27e367fcdc01';
        $optionId = '01990bc74c8f7449a79486b1fd9a95d5';
        $groupTechnicalName = 'CALC_' . strtoupper($axis);

        $configurator = new SalesChannelPartsListConfiguratorEntity();
        $configurator->setMapping([$groupTechnicalName => $groupId]);

        $option = new PropertyGroupOptionEntity();
        $option->setId($optionId);
        $option->setGroupId($groupId);
        $option->addTranslated('customFields', [$customField => 17]);

        $product = new ProductEntity();
        $product->setId('01990bc77c047523960b3054415ff827');
        $product->setOptionIds([$optionId]);

        $item = PartsListEntity::createFromProduct($product);
        $partsList = new PartsListCollection([$item]);

        $calculator = $this->createStub(PartsListCalculatorInterface::class);
        $calculator->method($calculatorMethod)->willReturn(true);

        $loader = (new \ReflectionClass(PartsListConfiguratorPageLoader::class))
            ->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($loader, 'enrichPartsList');
        $method->invoke(
            $loader,
            $configurator,
            $calculator,
            $partsList,
            new ProductStreamCollection(),
            new PropertyGroupOptionCollection([$option])
        );

        static::assertSame(17, $item->{$getter}());
        static::assertSame(0, $item->getCalcX());
    }

    public static function axisProvider(): iterable
    {
        yield 'Y axis' => ['y', 'moorl_pl_calc_y_value', 'isCalcY', 'getCalcY'];
        yield 'Z axis' => ['z', 'moorl_pl_calc_z_value', 'isCalcZ', 'getCalcZ'];
    }

    public function testConfiguredSeoMetadataTakesPrecedenceOverFallbacks(): void
    {
        $configurator = new SalesChannelPartsListConfiguratorEntity();
        $configurator->addTranslated('metaDescription', 'Configured description');
        $configurator->addTranslated('teaser', 'Fallback description');
        $configurator->addTranslated('metaTitle', 'Configured title');
        $configurator->addTranslated('name', 'Fallback title');

        $page = new PartsListConfiguratorPage();
        $page->setPartsListConfigurator($configurator);
        $page->setMetaInformation(new MetaInformation());

        $loader = (new \ReflectionClass(PartsListConfiguratorPageLoader::class))
            ->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($loader, 'loadMetaData');
        $method->invoke($loader, $page);

        static::assertSame('Configured description', $page->getMetaInformation()?->getMetaDescription());
        static::assertSame('Configured title', $page->getMetaInformation()?->getMetaTitle());
    }

    public function testSeoMetadataFallsBackToTeaserAndName(): void
    {
        $configurator = new SalesChannelPartsListConfiguratorEntity();
        $configurator->addTranslated('teaser', 'Fallback description');
        $configurator->addTranslated('name', 'Fallback title');

        $page = new PartsListConfiguratorPage();
        $page->setPartsListConfigurator($configurator);
        $page->setMetaInformation(new MetaInformation());

        $loader = (new \ReflectionClass(PartsListConfiguratorPageLoader::class))
            ->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($loader, 'loadMetaData');
        $method->invoke($loader, $page);

        static::assertSame('Fallback description', $page->getMetaInformation()?->getMetaDescription());
        static::assertSame('Fallback title', $page->getMetaInformation()?->getMetaTitle());
    }

    public function testPropertyFilterUsesKnownOptionGroupAssignments(): void
    {
        $optionA = '01990bc70f3a7c13a09f27e367fcdc01';
        $optionB = '01990bc74c8f7449a79486b1fd9a95d5';
        $optionC = '01990bc77c047523960b3054415ff827';
        $unknownOption = '01990bc77c047523960b3054415ff828';
        $groupA = '01990bc77c047523960b3054415ff829';
        $groupB = '01990bc77c047523960b3054415ff830';

        $loader = (new \ReflectionClass(PartsListConfiguratorPageLoader::class))
            ->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($loader, 'getPropertyFilterByIds');
        $filter = $method->invoke(
            $loader,
            [$optionA, $optionB, $optionC, $unknownOption],
            [
                $optionA => $groupA,
                $optionB => $groupA,
                $optionC => $groupB,
            ]
        );

        $groupFilters = $filter->getQueries();

        static::assertCount(2, $groupFilters);
        static::assertContainsOnlyInstancesOf(OrFilter::class, $groupFilters);
        $this->assertOptionFilter($groupFilters[0], [$optionA, $optionB]);
        $this->assertOptionFilter($groupFilters[1], [$optionC]);
    }

    /**
     * @param list<string> $expectedOptionIds
     */
    private function assertOptionFilter(OrFilter $filter, array $expectedOptionIds): void
    {
        $queries = $filter->getQueries();

        static::assertCount(2, $queries);
        static::assertContainsOnlyInstancesOf(EqualsAnyFilter::class, $queries);
        static::assertSame('product.optionIds', $queries[0]->getField());
        static::assertSame($expectedOptionIds, $queries[0]->getValue());
        static::assertSame('product.propertyIds', $queries[1]->getField());
        static::assertSame($expectedOptionIds, $queries[1]->getValue());
    }
}
