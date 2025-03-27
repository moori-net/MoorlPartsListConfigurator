<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Moorl\PartsListConfigurator\Core\Calculator\PartsListCalculatorInterface;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\PartsListConfiguratorDetailRoute;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
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
     * @param PartsListCalculatorInterface[] $partsListCalculators
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly PartsListConfiguratorDetailRoute $partsListConfiguratorDetailRoute,
        private readonly AbstractProductListingRoute $productListingRoute,
        private readonly CartService $cartService,
        private readonly Connection $connection,
        private readonly iterable $partsListCalculators
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

        if ($partsListConfigurator->getFilters()->count() === 0) {
            throw new PageNotFoundException($partsListConfigurator->getId());
        }

        $partsListConfigurator->getFilters()->sortByPosition();
        $partsListConfigurator->setCurrentOptionIds($this->getPropIds($request, 'options'));

        $calculator = $this->getPartsListCalculatorByName($partsListConfigurator->getCalculator());

        $productStreams = new ProductStreamCollection();
        foreach ($partsListConfigurator->getFilters() as $filter) {
            $productStreams->merge($filter->getProductStreams());
        }

        $mainFilters = [];
        foreach ($productStreams as $productStream) {
            $streamFilter = new ContainsFilter('streamIds', $productStream->getId());
            $propertyFilters = [];

            foreach ($partsListConfigurator->getFilters() as $filter) {
                if ($filter->getLogical()) {
                    if ($filter->getProductStreams()->has($productStream->getId())) {
                        //continue;
                    }

                    if ($filter->getLogicalConfigurator()) {
                        continue;
                    }

                    // Ein logischer Filter sollte nur eine Gruppe haben,
                    // weil der logische Konfigurator des Filters anhand des technischen Namens der Gruppe geladen wird
                    $filter->setLogicalConfigurator($calculator->getLogicalConfigurator(
                        $request,
                        $salesChannelContext,
                        $partsListConfigurator,
                        $filter->getGroupTechnicalName()
                    ));

                    continue;
                }

                $optionIds = array_values($filter->getOptions()?->getIds() ?: []);

                if ($filter->getProductStreams()->has($productStream->getId())) {
                    $propertyFilters[] = $this->getPropertyFilter($request, $optionIds, 'options', $filter->getFixed());
                }
            }

            if ($productStream->getTranslation('customFields')['moorl_pl_optional'] ?? false) {
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

        $criteria->setLimit(100);

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
        $page->setCalculator($this->getPartsListCalculatorByName($partsListConfigurator->getCalculator()));

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

    private function getPartsListCalculatorByName(string $name): PartsListCalculatorInterface
    {
        foreach ($this->partsListCalculators as $partsListCalculator) {
            if ($partsListCalculator->getName() === $name) {
                return $partsListCalculator;
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
