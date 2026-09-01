<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Tests\Storefront\Controller;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterCollection;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Storefront\Controller\PartsListConfiguratorController;
use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPage;
use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPageLoader;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\HttpFoundation\Request;
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
}
