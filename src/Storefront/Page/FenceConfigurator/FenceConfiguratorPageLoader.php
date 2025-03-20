<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Storefront\Page\FenceConfigurator;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\FenceConfiguratorDetailRoute;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class FenceConfiguratorPageLoader
{
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly FenceConfiguratorDetailRoute $fenceConfiguratorDetailRoute
    )
    {
    }

    public function load(Request $request, SalesChannelContext $context): FenceConfiguratorPage
    {
        $fenceConfiguratorId = $request->attributes->get('fenceConfiguratorId');
        if (!$fenceConfiguratorId) {
            throw RoutingException::missingRequestParameter('fenceConfiguratorId');
        }
        
        $criteria = new Criteria();
        $result = $this->fenceConfiguratorDetailRoute->load($fenceConfiguratorId, $request, $context, $criteria);
        $fenceConfigurator = $result->getFenceConfigurator();

        if (!$fenceConfigurator->getActive()) {
            throw new PageNotFoundException($fenceConfigurator->getId());
        }

        $page = $this->genericLoader->load($request, $context);

        /** @var FenceConfiguratorPage $page */
        $page = FenceConfiguratorPage::createFrom($page);
        $page->setFenceConfigurator($fenceConfigurator);
        $page->setCmsPage($fenceConfigurator->getCmsPage());

        $this->loadMetaData($page);

        return $page;
    }

    private function loadMetaData(FenceConfiguratorPage $page): void
    {
        $metaInformation = $page->getMetaInformation();
        if (!$metaInformation) {
            return;
        }

        $metaDescription = $page->getFenceConfigurator()->getTranslation('teaser')
            ?? $page->getFenceConfigurator()->getTranslation('teaser');
        $metaInformation->setMetaDescription((string) $metaDescription);

        if ((string) $page->getFenceConfigurator()->getTranslation('name') !== '') {
            $metaInformation->setMetaTitle((string) $page->getFenceConfigurator()->getTranslation('name'));
            return;
        }

        $metaTitleParts = [$page->getFenceConfigurator()->getTranslation('name')];
        $metaInformation->setMetaTitle(implode(' | ', $metaTitleParts));
    }
}
