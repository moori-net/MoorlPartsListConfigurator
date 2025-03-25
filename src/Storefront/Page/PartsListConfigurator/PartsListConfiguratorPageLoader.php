<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Moorl\PartsListConfigurator\Core\Calculator\CalculatorInterface;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\PartsListConfiguratorDetailRoute;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;

class PartsListConfiguratorPageLoader
{
    public const CRITERIA_STATE = 'moorl-parts-list-configurator-criteria';

    /**
     * @param CalculatorInterface[] $calculators
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly PartsListConfiguratorDetailRoute $partsListConfiguratorDetailRoute,
        private readonly AbstractProductListingRoute $productListingRoute,
        private readonly CartService $cartService,
        private readonly Connection $connection,
        private readonly iterable $calculators
    )
    {
    }

    public function getCartService(): CartService
    {
        return $this->cartService;
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext): PartsListConfiguratorPage
    {
        $partsListConfiguratorId = $request->attributes->get('partsListConfiguratorId');
        if (!$partsListConfiguratorId) {
            throw RoutingException::missingRequestParameter('partsListConfiguratorId');
        }
        
        $criteria = new Criteria();
        $result = $this->partsListConfiguratorDetailRoute->load($partsListConfiguratorId, $request, $salesChannelContext, $criteria);
        /** @var SalesChannelPartsListConfiguratorEntity $partsListConfigurator */
        $partsListConfigurator = $result->getPartsListConfigurator();

        if (!$partsListConfigurator->getActive()) {
            throw new PageNotFoundException($partsListConfigurator->getId());
        }

        if ($partsListConfigurator->getPartsListConfiguratorProductStreams()->count() === 0) {
            throw new PageNotFoundException($partsListConfigurator->getId());
        }

        if ($partsListConfigurator->getFilters()->count() === 0) {
            throw new PageNotFoundException($partsListConfigurator->getId());
        }

        $partsListConfigurator->getFilters()->sortByPosition();
        $partsListConfigurator->setCurrentOptionIds($this->getPropIds($request, 'options'));

        $calculator = $this->getCalculatorByName($partsListConfigurator->getCalculator());

        $mainFilters = [];
        foreach ($partsListConfigurator->getPartsListConfiguratorProductStreams() as $partsListConfiguratorProductStream) {
            $streamFilter = new ContainsFilter('streamIds', $partsListConfiguratorProductStream->getProductStreamId());
            $propertyFilters = [];

            foreach ($partsListConfigurator->getFilters() as $filter) {
                if ($filter->getLogical()) {
                    if (!in_array($partsListConfiguratorProductStream->getId(), $filter->getPartsListConfiguratorProductStreamIds())) {
                        continue;
                    }

                    $filter->addProductStreamId($partsListConfiguratorProductStream->getProductStreamId());

                    $filter->setLogicalConfigurator($calculator->getLogicalConfigurator(
                        $request,
                        $salesChannelContext,
                        $partsListConfigurator,
                        $filter->getOptions()->getGroups()->first()->getTranslation('customFields')['moorl_pl_name']
                    ));

                    continue;
                }
                $optionIds = array_values($filter->getOptions()?->getIds() ?: []);

                if (in_array($partsListConfiguratorProductStream->getId(), $filter->getPartsListConfiguratorProductStreamIds())) {
                    $propertyFilters[] = $this->getPropertyFilter($request, $optionIds, 'options', $filter->getFixed());
                }
            }

            if ($partsListConfiguratorProductStream->getAccessory()) {
                $mainFilters[] = new AndFilter([
                    $streamFilter,
                    new OrFilter([
                        new AndFilter($propertyFilters),
                        new EqualsFilter('parentId', null)
                    ])
                ]);
            } else {
                $propertyFilters[] = $streamFilter;

                $mainFilters[] = new AndFilter($propertyFilters);
            }
        }

        $criteria = new Criteria();
        $criteria->addState(self::CRITERIA_STATE);
        $criteria->addPostFilter(new AndFilter([
            new OrFilter($mainFilters)
        ]));

        $result = $this->productListingRoute->load(
            $salesChannelContext->getSalesChannel()->getNavigationCategoryId(),
            $request,
            $salesChannelContext,
            $criteria
        );
        $products = $result->getResult();

        $partsList = PartsListCollection::createFromProducts($products->getEntities());

        $page = $this->genericLoader->load($request, $salesChannelContext);

        /** @var PartsListConfiguratorPage $page */
        $page = PartsListConfiguratorPage::createFrom($page);
        $page->setPartsListConfigurator($partsListConfigurator);
        $page->setCmsPage($partsListConfigurator->getCmsPage());
        $page->setProducts($products);
        $page->setPartsList($partsList);
        $page->setCalculator($this->getCalculatorByName($partsListConfigurator->getCalculator()));

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

    private function getFilterProductStreamIds(
        Request $request,
        string $prop = "tag"
    ): ?array
    {
        $ids = $this->getPropIds($request, $prop);
        if (empty($ids)) {
            return new AndFilter([]);
        }
    }

    private function getPropertyFilter(
        Request $request,
        array $whitelistIds = [],
        string $prop = "tag",
        bool $fixed = false,
    ): AndFilter
    {
        if ($fixed) {
            $ids = $whitelistIds;
        } else {
            $ids = $this->getPropIds($request, $prop);
            if (empty($ids)) {
                return new AndFilter([]);
            }

            $ids = array_intersect($ids, $whitelistIds);
        }

        $grouped = $this->connection->fetchAllAssociative(
            'SELECT LOWER(HEX(property_group_id)) as property_group_id, LOWER(HEX(id)) as id FROM property_group_option WHERE id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::BINARY]
        );

        $grouped = FetchModeHelper::group($grouped, static fn ($row): string => (string) $row['id']);

        $filters = [];
        foreach ($grouped as $options) {
            $filters[] = new OrFilter([
                new EqualsAnyFilter('product.optionIds', $options),
                new EqualsAnyFilter('product.propertyIds', $options),
            ]);
        }

        return new AndFilter($filters);
    }

    private function getCalculatorByName(string $name): CalculatorInterface
    {
        foreach ($this->calculators as $calculator) {
            if ($calculator->getName() === $name) {
                return $calculator;
            }
        }
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
