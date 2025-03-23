<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Controller;

use Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator\PartsListConfiguratorPageLoader;
use MoorlFoundation\Core\Content\ProductBuyList\ProductBuyListItemCollection;
use MoorlFoundation\Core\Content\ProductBuyList\ProductBuyListItemEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class PartsListConfiguratorController extends StorefrontController
{
    public function __construct(private readonly PartsListConfiguratorPageLoader $partsListConfiguratorPageLoader)
    {
    }

    #[Route(path: '/parts-list-configurator/{partsListConfiguratorId}', name: 'frontend.moorl.parts.list.configurator.detail', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function detail(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $page = $this->partsListConfiguratorPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/page/content/parts-list-configurator-detail.html.twig', [
            'page' => $page
        ]);
    }

    #[Route(path: '/parts-list-configurator/{partsListConfiguratorId}/parts-list', name: 'frontend.moorl.parts.list.configurator.parts.list', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function partsList(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $page = $this->partsListConfiguratorPageLoader->load($request, $salesChannelContext);

        $items = new ProductBuyListItemCollection();

        /** @var SalesChannelProductEntity $product */
        foreach ($page->getProducts() as $product) {
            $item = new ProductBuyListItemEntity();
            $item->setId($product->getId());
            $item->setProductId($product->getId());
            $item->setProduct($product);
            $item->setQuantity(12);

            $items->add($item);
        }

        return $this->renderStorefront('@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/component/parts-list.html.twig', [
            'items' => $items
        ]);
    }

    #[Route(path: '/parts-list-configurator/{partsListConfiguratorId}/logical-configurator', name: 'frontend.moorl.parts.list.configurator.logical.configurator', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function logicalConfigurator(SalesChannelContext $salesChannelContext, Request $request): Response
    {
        $page = $this->partsListConfiguratorPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@MoorlPartsListConfigurator/plugin/moorl-parts-list-configurator/component/logical-configurator.html.twig', [
            'page' => $page,
            'logicalConfigurator' => $page->getCalculator()->getLogicalConfigurator($request, $salesChannelContext, $page->getPartsListConfigurator()),
        ]);
    }
}
