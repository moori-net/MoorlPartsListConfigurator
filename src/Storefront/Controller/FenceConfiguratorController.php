<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Storefront\Controller;

use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FenceConfiguratorController extends StorefrontController
{
    public function __construct(
        private readonly StringTemplateRenderer $templateRenderer
    )
    {
    }

    #[Route(path: '/fence-configurator/{fenceConfiguratorId}', name: 'frontend.moorl.fence.configurator.detail', methods: ['GET'], defaults: ['XmlHttpRequest' => true])]
    public function detail(SalesChannelContext $context, Request $request): Response
    {
        $page = $this->creatorPageLoader->load($request, $context);

        return $this->renderStorefront('@MoorlFenceConfigurator/plugin/moorl-fence-configurator/page/content/fence-configurator-detail.html.twig', [
            'page' => $page
        ]);
    }
}
