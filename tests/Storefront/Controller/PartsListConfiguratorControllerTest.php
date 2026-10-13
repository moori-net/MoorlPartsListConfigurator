<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Tests\Storefront\Controller;

use Moorl\PartsListConfigurator\Core\Calculator\DemoFenceCalculator2;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterCollection;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterEntity;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use Moorl\PartsListConfigurator\Storefront\Controller\PartsListConfiguratorController;
use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPage;
use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPageLoader;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use MoorlFoundation\Core\Content\PartsList\PartsListEntity;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PartsListConfiguratorControllerTest extends TestCase
{
    public function testAvailabilityRouteEnablesShopwareHttpCache(): void
    {
        $routes = (new AttributeRouteControllerLoader())->load(PartsListConfiguratorController::class);
        $route = $routes->get('frontend.moorl.parts.list.configurator.availability');

        static::assertNotNull($route);
        static::assertTrue($route->getDefault(PlatformRequest::ATTRIBUTE_HTTP_CACHE));
    }

    public function testUnknownLogicalGroupProducesNotFoundResponse(): void
    {
        $configurator = new SalesChannelPartsListConfiguratorEntity();
        $configurator->setFilters(new PartsListConfiguratorFilterCollection());

        $page = new PartsListConfiguratorPage();
        $page->setPartsListConfigurator($configurator);
        $page->setPartsList(new PartsListCollection());

        $pageLoader = $this->createStub(PartsListConfiguratorPageLoader::class);
        $pageLoader->method('load')->willReturn($page);

        $controller = new PartsListConfiguratorController($pageLoader);
        $context = (new \ReflectionClass(SalesChannelContext::class))->newInstanceWithoutConstructor();
        $request = new Request(['group' => 'UNKNOWN_GROUP']);

        $this->expectException(NotFoundHttpException::class);

        $controller->logicalConfigurator($context, $request);
    }

    public function testAccessoryListSeparatesItemsByOptionalProductStream(): void
    {
        $optionalAccessories = $this->createProductStream('optional-accessories-id', 'Optionale Zubehörteile', ['optional']);
        $layoutAccessories = $this->createProductStream('layout-accessories-id', 'Zubehör für die Gestaltung', ['optional']);

        $filter = new PartsListConfiguratorFilterEntity();
        $filter->setId('accessory-filter');
        $filter->setProductStreams(new ProductStreamCollection([$optionalAccessories, $layoutAccessories]));

        $configurator = new SalesChannelPartsListConfiguratorEntity();
        $configurator->setMapping([
            'OPTIONAL_ACCESSORIES' => 'optional-accessories-id',
            'LAYOUT_ACCESSORIES' => 'layout-accessories-id',
        ]);
        $configurator->setFilters(new PartsListConfiguratorFilterCollection([$filter]));

        $optionalAccessory = $this->createPartsListItem('optional-accessory', 'OPTIONAL_ACCESSORIES', 'optional-accessories-id');
        $layoutAccessory = $this->createPartsListItem('layout-accessory', 'LAYOUT_ACCESSORIES', 'layout-accessories-id');
        $fence = $this->createPartsListItem('fence', 'FENCES', 'fence-id');

        $page = new PartsListConfiguratorPage();
        $page->setCalculator(new DemoFenceCalculator2($this->createStub(PartsListService::class)));
        $page->setPartsListConfigurator($configurator);
        $page->setPartsList(new PartsListCollection([$optionalAccessory, $layoutAccessory, $fence]));

        $pageLoader = $this->createStub(PartsListConfiguratorPageLoader::class);
        $pageLoader->method('load')->willReturn($page);

        $controller = new class($pageLoader) extends PartsListConfiguratorController {
            public string $view;
            public array $parameters;

            protected function renderStorefront(string $view, array $parameters = []): Response
            {
                $this->view = $view;
                $this->parameters = $parameters;

                return new Response();
            }
        };

        $context = (new \ReflectionClass(SalesChannelContext::class))->newInstanceWithoutConstructor();
        $controller->accessoryList($context, new Request());

        static::assertSame(
            '@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/component/accessory-list.html.twig',
            $controller->view
        );
        static::assertSame(['OPTIONAL_ACCESSORIES', 'LAYOUT_ACCESSORIES'], array_keys($controller->parameters['accessoryLists']));
        static::assertSame('Optionale Zubehörteile', $controller->parameters['accessoryLists']['OPTIONAL_ACCESSORIES']['title']);
        static::assertSame('Zubehör für die Gestaltung', $controller->parameters['accessoryLists']['LAYOUT_ACCESSORIES']['title']);
        static::assertSame([$optionalAccessory], array_values($controller->parameters['accessoryLists']['OPTIONAL_ACCESSORIES']['items']->getElements()));
        static::assertSame([$layoutAccessory], array_values($controller->parameters['accessoryLists']['LAYOUT_ACCESSORIES']['items']->getElements()));
    }

    private function createProductStream(string $id, string $name, array $flags): ProductStreamEntity
    {
        $productStream = new ProductStreamEntity();
        $productStream->setId($id);
        $productStream->addTranslated('name', $name);
        $productStream->addTranslated('flags', $flags);

        return $productStream;
    }

    private function createPartsListItem(string $id, string $technicalName, string $productStreamId): PartsListEntity
    {
        $product = new ProductEntity();
        $product->setId($id);
        $product->setStreamIds([$productStreamId]);

        $item = PartsListEntity::createFromProduct($product);
        $item->addProductStream($technicalName);

        return $item;
    }
}
