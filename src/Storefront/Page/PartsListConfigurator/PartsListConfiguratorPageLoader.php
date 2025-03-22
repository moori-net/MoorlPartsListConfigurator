<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\PartsListConfiguratorDetailRoute;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class PartsListConfiguratorPageLoader
{
    public const CRITERIA_STATE = 'moorl-parts-list-configurator-criteria';

    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly PartsListConfiguratorDetailRoute $partsListConfiguratorDetailRoute,
        private readonly AbstractProductListingRoute $productListingRoute
    )
    {
    }

    public function load(Request $request, SalesChannelContext $context): PartsListConfiguratorPage
    {
        $partsListConfiguratorId = $request->attributes->get('partsListConfiguratorId');
        if (!$partsListConfiguratorId) {
            throw RoutingException::missingRequestParameter('partsListConfiguratorId');
        }
        
        $criteria = new Criteria();
        $result = $this->partsListConfiguratorDetailRoute->load($partsListConfiguratorId, $request, $context, $criteria);
        $partsListConfigurator = $result->getPartsListConfigurator();

        if (!$partsListConfigurator->getActive()) {
            throw new PageNotFoundException($partsListConfigurator->getId());
        }

        $request->query->set('no-aggregations', 1);

        $optionIds = $partsListConfigurator->getFixedOptions()?->getIds() ?: [];
        $optionIds = array_merge($optionIds, $this->getPropIds($request, 'globalOptions'));

        $criteria = new Criteria();
        $criteria->addState(self::CRITERIA_STATE);
        $criteria->addFilter(new AndFilter([
            new EqualsAnyFilter('options.id', $optionIds),
            new OrFilter([
                new AndFilter([
                    new EqualsAnyFilter('options.id', $this->getPropIds($request, 'firstOptions')),
                    new ContainsFilter('streamIds', $partsListConfigurator->getFirstStreamId())
                ]),
                new AndFilter([
                    new EqualsAnyFilter('options.id', $this->getPropIds($request, 'secondOptions')),
                    new ContainsFilter('streamIds', $partsListConfigurator->getSecondStreamId()),
                ]),
                new AndFilter([
                    new EqualsAnyFilter('options.id', $this->getPropIds($request, 'thirdOptions')),
                    new ContainsFilter('streamIds', $partsListConfigurator->getThirdStreamId())
                ]),
            ])
        ]));

        $result = $this->productListingRoute->load(
            $context->getSalesChannel()->getNavigationCategoryId(),
            $request,
            $context,
            $criteria
        );
        $products = $result->getResult();

        $page = $this->genericLoader->load($request, $context);

        /** @var PartsListConfiguratorPage $page */
        $page = PartsListConfiguratorPage::createFrom($page);
        $page->setPartsListConfigurator($partsListConfigurator);
        $page->setCmsPage($partsListConfigurator->getCmsPage());

        $page->setProducts($products);

        $this->loadMetaData($page);

        return $page;
    }

    private function loadMetaData(PartsListConfiguratorPage $page): void
    {
        $metaInformation = $page->getMetaInformation();
        if (!$metaInformation) {
            return;
        }

        $metaDescription = $page->getPartsListConfigurator()->getTranslation('teaser')
            ?? $page->getPartsListConfigurator()->getTranslation('teaser');
        $metaInformation->setMetaDescription((string) $metaDescription);

        if ((string) $page->getPartsListConfigurator()->getTranslation('name') !== '') {
            $metaInformation->setMetaTitle((string) $page->getPartsListConfigurator()->getTranslation('name'));
            return;
        }

        $metaTitleParts = [$page->getPartsListConfigurator()->getTranslation('name')];
        $metaInformation->setMetaTitle(implode(' | ', $metaTitleParts));
    }

    protected function getPropIds(Request $request, string $prop = "tag", ?array $defaultIds = null): array
    {
        $ids = $request->query->get($prop);
        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get($prop);
        }

        if (\is_string($ids)) {
            $ids = explode('|', $ids);
        }

        if (empty($ids) && !empty($defaultIds)) {
            $ids = $defaultIds;
        }

        $ids = array_filter((array) $ids, function ($id) {
            return Uuid::isValid((string) $id);
        });

        return $ids;
    }
}
