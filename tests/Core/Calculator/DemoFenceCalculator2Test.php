<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Tests\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Calculator\DemoFenceCalculator2;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterCollection;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterEntity;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use MoorlFoundation\Core\Content\PartsList\PartsListEntity;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DemoFenceCalculator2Test extends TestCase
{
    public function testItBuildsTheLayoutFromTheSelectedOption(): void
    {
        $calculator = new DemoFenceCalculator2($this->createStub(PartsListService::class));
        $logicalConfigurator = $calculator->getLogicalConfigurator(
            new Request(),
            (new \ReflectionClass(SalesChannelContext::class))->newInstanceWithoutConstructor(),
            $this->createLayoutConfigurator(2, 1),
            'PARTS_LIST_LAYOUT'
        );

        static::assertSame(['side_a', 'side_b', 'side_c', 'side_d'], $logicalConfigurator['elements']);
        static::assertSame(2, $logicalConfigurator['cornerPostQuantity']);
        static::assertSame(1, $logicalConfigurator['flexCornerPostQuantity']);
    }

    public function testItCountsCornerPostsFromTheSelectedLayoutOption(): void
    {
        $cornerPost = $this->createPost('corner-post', 'PARTS_LIST_POST_TYPE_CORNER');
        $flexCornerPost = $this->createPost('flex-corner-post', 'PARTS_LIST_POST_TYPE_FLEX_CORNER');
        $sidePost = $this->createPost('side-post', 'PARTS_LIST_POST_TYPE_SIDE');

        $calculator = new DemoFenceCalculator2($this->createStub(PartsListService::class));
        $shortestFence = new \ReflectionProperty($calculator, 'shortestFence');
        $shortestFence->setValue($calculator, 1000);

        $calculator->calculatePartsList(
            new Request([
                'side_a_length' => 1,
                'side_b_length' => 1,
                'side_c_length' => 1,
                'side_d_length' => 1,
            ]),
            (new \ReflectionClass(SalesChannelContext::class))->newInstanceWithoutConstructor(),
            $this->createLayoutConfigurator(2, 1),
            new PartsListCollection([$cornerPost, $flexCornerPost, $sidePost])
        );

        static::assertSame(2, $cornerPost->getQuantity());
        static::assertSame(1, $flexCornerPost->getQuantity());
    }

    private function createPost(string $id, string $option): PartsListEntity
    {
        $post = new PartsListEntity();
        $post->setId($id);
        $post->addOption($option);

        return $post;
    }

    private function createLayoutConfigurator(int $cornerPostQuantity, int $flexCornerPostQuantity): SalesChannelPartsListConfiguratorEntity
    {
        $layoutGroupId = 'layout-group';
        $layoutOptionId = 'layout-option';

        $layoutOption = new PropertyGroupOptionEntity();
        $layoutOption->setId($layoutOptionId);
        $layoutOption->setGroupId($layoutGroupId);
        $layoutOption->addTranslated('customFields', [
            'moorl_pl_calc_x_value' => $cornerPostQuantity,
            'moorl_pl_calc_y_value' => $flexCornerPostQuantity,
        ]);

        $filter = new PartsListConfiguratorFilterEntity();
        $filter->setId('layout-filter');
        $filter->setPropertyGroupOptions(new PropertyGroupOptionCollection([$layoutOption]));

        $configurator = new SalesChannelPartsListConfiguratorEntity();
        $configurator->setMapping([
            'PARTS_LIST_LAYOUT' => $layoutGroupId,
        ]);
        $configurator->setCurrentOptionIds([$layoutOptionId]);
        $configurator->setFilters(new PartsListConfiguratorFilterCollection([$filter]));

        return $configurator;
    }
}
