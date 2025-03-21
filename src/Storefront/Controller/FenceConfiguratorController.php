<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Storefront\Controller;

use Moorl\FenceConfigurator\Storefront\Page\FenceConfigurator\FenceConfiguratorPageLoader;
use MoorlFoundation\Core\Content\ProductBuyListV2Item\ProductBuyListV2ItemCollection;
use MoorlFoundation\Core\Content\ProductBuyListV2Item\ProductBuyListV2ItemEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FenceConfiguratorController extends StorefrontController
{
    public function __construct(private readonly FenceConfiguratorPageLoader $fenceConfiguratorPageLoader)
    {
    }

    #[Route(path: '/fence-configurator/{fenceConfiguratorId}', name: 'frontend.moorl.fence.configurator.detail', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function detail(SalesChannelContext $context, Request $request): Response
    {
        $page = $this->fenceConfiguratorPageLoader->load($request, $context);

        return $this->renderStorefront('@MoorlFenceConfigurator/plugin/moorl-fence-configurator/page/content/fence-configurator-detail.html.twig', [
            'page' => $page
        ]);
    }

    #[Route(path: '/fence-configurator/{fenceConfiguratorId}/parts-list', name: 'frontend.moorl.fence.configurator.parts.list', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function partsList(SalesChannelContext $context, Request $request): Response
    {
        $page = $this->fenceConfiguratorPageLoader->load($request, $context);

        $items = new ProductBuyListV2ItemCollection();

        /** @var SalesChannelProductEntity $product */
        foreach ($page->getProducts() as $product) {
            $item = new ProductBuyListV2ItemEntity();
            $item->setId($product->getId());
            $item->setProductId($product->getId());
            $item->setProduct($product);
            $item->setQuantity(12);

            $items->add($item);
        }

        return $this->renderStorefront('@MoorlFenceConfigurator/plugin/moorl-fence-configurator/component/parts-list.html.twig', [
            'items' => $items
        ]);
    }
}
