<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Controller;

use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPageLoader;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class PartsListConfiguratorController extends StorefrontController
{
    public function __construct(
        private readonly PartsListConfiguratorPageLoader $partsListConfiguratorPageLoader
    )
    {
    }

    #[Route(path: '/parts-list-configurator/{partsListConfiguratorId}', name: 'frontend.moorl.parts.list.configurator.detail', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function detail(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $page = $this->partsListConfiguratorPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/page/content/parts-list-configurator-detail.html.twig', [
            'page' => $page,
            'partsList' => $page->getPartsList(),
            'accessoryList' => $page->getPartsList()->filterByProductStreamIds($page->getPartsListConfigurator()->getAccessoryProductStreamIds())
        ]);
    }

    #[Route(path: '/parts-list-configurator/{partsListConfiguratorId}/parts-list', name: 'frontend.moorl.parts.list.configurator.parts.list', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function partsList(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $page = $this->partsListConfiguratorPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/component/parts-list.html.twig', [
            'partsList' => $page->getPartsList()
        ]);
    }

    #[Route(path: '/parts-list-configurator/{partsListConfiguratorId}/logical-configurator', name: 'frontend.moorl.parts.list.configurator.logical.configurator', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function logicalConfigurator(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $page = $this->partsListConfiguratorPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/component/logical-configurator.html.twig', [
            'page' => $page,
            'accessoryList' => $page->getPartsList()->filterByProductStreamIds($page->getPartsListConfigurator()->getAccessoryProductStreamIds()),
            'logicalConfigurator' => $page->getCalculator()->getLogicalConfigurator($request, $salesChannelContext, $page->getPartsListConfigurator()),
        ]);
    }
}
