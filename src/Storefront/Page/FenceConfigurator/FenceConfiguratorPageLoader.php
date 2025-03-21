<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Storefront\Page\FenceConfigurator;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\FenceConfiguratorDetailRoute;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class FenceConfiguratorPageLoader
{
    public const CRITERIA_STATE = 'moorl-fence-configurator-criteria';

    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly FenceConfiguratorDetailRoute $fenceConfiguratorDetailRoute,
        private readonly AbstractProductListingRoute $productListingRoute
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

        $criteria = new Criteria();
        $criteria->addState(self::CRITERIA_STATE);
        $criteria->addFilter(new AndFilter([
            new OrFilter([
                new AndFilter([
                    new ContainsFilter('optionIds', $fenceConfigurator->getProductLinePropertyId()),
                    new ContainsFilter('streamIds', $fenceConfigurator->getFenceStreamId())
                ]),
                new ContainsFilter('streamIds', $fenceConfigurator->getFencePostStreamId()),
                new ContainsFilter('streamIds', $fenceConfigurator->getFenceOtherStreamId())
            ])
        ]));

        //dd($request->query->all());

        if ($request->query->get('options')) {
            $request->query->set('properties', $request->query->get('options'));
            $request->query->set('no-aggregations', 1);
        }

        $result = $this->productListingRoute->load(
            $context->getSalesChannel()->getNavigationCategoryId(),
            $request,
            $context,
            $criteria
        );
        $products = $result->getResult();

        $page = $this->genericLoader->load($request, $context);

        /** @var FenceConfiguratorPage $page */
        $page = FenceConfiguratorPage::createFrom($page);
        $page->setFenceConfigurator($fenceConfigurator);
        $page->setCmsPage($fenceConfigurator->getCmsPage());

        $page->setProducts($products);

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
