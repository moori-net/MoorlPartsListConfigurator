<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Moorl\PartsListConfigurator\Core\Calculator\CoreCalculator;
use Moorl\PartsListConfigurator\Core\Calculator\PartsListCalculatorInterface;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\PartsListConfiguratorDetailRoute;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use MoorlFoundation\Core\Content\PartsList\PartsListEntity;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
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
    public const OPT_PROXY_CART = 'proxy-cart'; // Warenkorb berechnen
    public const OPT_NO_PARENT = 'no-parent'; // Stückliste wird manuell eingegeben - Varianten können mehrfach vorkommen
    public const OPT_CALCULATE = 'calculate'; // Berechnungen durchführen

    /**
     * @param PartsListCalculatorInterface[] $partsListCalculators
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly PartsListConfiguratorDetailRoute $partsListConfiguratorDetailRoute,
        private readonly AbstractProductListingRoute $productListingRoute,
        private readonly CartService $cartService,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly iterable $partsListCalculators
    ){}

    public function getCartService(): CartService
    {
        return $this->cartService;
    }

    /**
     * @return list<string>
     */
    public function availability(
        Request $request,
        SalesChannelContext $salesChannelContext
    ): array {
        $partsListConfiguratorId = $request->attributes->get('partsListConfiguratorId');
        if (!$partsListConfiguratorId) {
            throw RoutingException::missingRequestParameter('partsListConfiguratorId');
        }

        $criteria = new Criteria();

        $result = $this->partsListConfiguratorDetailRoute->load(
            $partsListConfiguratorId,
            $request,
            $salesChannelContext,
            $criteria
        );

        /** @var SalesChannelPartsListConfiguratorEntity $partsListConfigurator */
        $partsListConfigurator = $result->getPartsListConfigurator();

        if (!$partsListConfigurator->getActive()) {
            throw new PageNotFoundException($partsListConfigurator->getId());
        }

        if ($partsListConfigurator->getFilters()->count() === 0) {
            return [];
        }

        $partsListConfigurator->getFilters()->sortByPosition();

        $selectedOptionIds = $this->getPropIds($request, 'options');

        $options = new PropertyGroupOptionCollection();

        foreach ($partsListConfigurator->getFilters() as $filter) {
            if ($filter->getLogical()) {
                continue;
            }

            $options->merge(
                $filter->getPropertyGroupOptions()
            );
        }

        $optionGroupIds = [];
        foreach ($options as $option) {
            $optionGroupIds[$option->getId()] = $option->getGroupId();
        }

        $availabilityOptionIds = $this->getPropIds(
            $request,
            'availabilityOptions'
        );

        $availableOptionIds = [];

        foreach ($availabilityOptionIds as $candidateOptionId) {
            $candidateOption = $options->get($candidateOptionId);
            if (!$candidateOption) {
                continue;
            }

            $candidateGroupId = $candidateOption->getGroupId();

            $productStreamIds = $this->getProductStreamIdsForOption(
                $partsListConfigurator,
                $candidateOptionId
            );

            if (empty($productStreamIds)) {
                continue;
            }

            $testOptionIds = array_filter(
                $selectedOptionIds,
                static fn (string $optionId): bool =>
                    ($optionGroupIds[$optionId] ?? null) !== $candidateGroupId
            );

            $testOptionIds[] = $candidateOptionId;

            $testOptionIds = array_values(
                array_unique($testOptionIds)
            );

            $isAvailable = true;

            foreach ($productStreamIds as $productStreamId) {
                $filters = $this->buildAvailabilityFilters(
                    $partsListConfigurator,
                    [$productStreamId],
                    $testOptionIds
                );

                if (empty($filters)) {
                    $isAvailable = false;
                    break;
                }

                $criteria = new Criteria();
                $criteria->addState(self::CRITERIA_STATE);
                $criteria->setLimit(1);

                $criteria->addFilter(
                    new AndFilter($filters)
                );

                $result = $this->productListingRoute->load(
                    $salesChannelContext
                        ->getSalesChannel()
                        ->getNavigationCategoryId(),
                    $request,
                    $salesChannelContext,
                    $criteria
                );

                if ($result->getResult()->count() === 0) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableOptionIds[] = $candidateOptionId;
            }
        }

        return array_values(
            array_unique($availableOptionIds)
        );
    }

    private function buildAvailabilityFilters(
        SalesChannelPartsListConfiguratorEntity $partsListConfigurator,
        array $productStreamIds,
        array $selectedOptionIds
    ): array {
        $mainFilters = [];

        foreach ($productStreamIds as $productStreamId) {
            $propertyFilters = [];

            foreach ($partsListConfigurator->getFilters() as $filter) {
                if ($filter->getLogical()) {
                    continue;
                }

                if (!$filter->getProductStreams()->has($productStreamId)) {
                    continue;
                }

                $whitelistIds = array_values(
                    $filter->getPropertyGroupOptions()?->getIds() ?: []
                );

                if ($filter->getFixed()) {
                    $optionIds = $whitelistIds;
                } else {
                    $optionIds = array_values(
                        array_intersect(
                            $selectedOptionIds,
                            $whitelistIds
                        )
                    );
                }

                if (empty($optionIds)) {
                    continue;
                }

                $propertyFilters[] = $this->getPropertyFilterByIds(
                    $optionIds
                );
            }

            $propertyFilters[] = new ContainsFilter(
                'streamIds',
                $productStreamId
            );

            $mainFilters[] = new AndFilter(
                $propertyFilters
            );
        }

        return $mainFilters;
    }

    /**
     * @return list<string>
     */
    private function getProductStreamIdsForOption(
        SalesChannelPartsListConfiguratorEntity $partsListConfigurator,
        string $optionId
    ): array {
        $productStreamIds = [];

        foreach ($partsListConfigurator->getFilters() as $filter) {
            if ($filter->getLogical()) {
                continue;
            }

            if (!$filter->getPropertyGroupOptions()?->has($optionId)) {
                continue;
            }

            $availabilityProductStreamIds = $filter->getAvailabilityProductStreams()->getIds();

            if (!empty($availabilityProductStreamIds)) {
                foreach ($availabilityProductStreamIds as $productStreamId) {
                    $productStreamIds[] = $productStreamId;
                }
            } else {
                foreach ($filter->getproductStreams()->getIds() as $productStreamId) {
                    $productStreamIds[] = $productStreamId;
                }
            }
        }

        return array_values(
            array_unique($productStreamIds)
        );
    }

    /**
     * @return array<string, bool>
     */
    private function resolveOptionAvailability(
        Request $request,
        SalesChannelContext $salesChannelContext,
        SalesChannelPartsListConfiguratorEntity $partsListConfigurator,
        ProductStreamCollection $productStreams,
        PropertyGroupOptionCollection $options
    ): array {
        $selectedOptionIds = $this->getPropIds($request, 'options');

        $optionGroupIds = [];

        foreach ($options as $option) {
            $optionGroupIds[$option->getId()] = $option->getGroupId();
        }

        $availability = [];

        foreach ($options as $candidate) {
            $candidateId = $candidate->getId();
            $candidateGroupId = $candidate->getGroupId();

            $candidateOptionIds = array_filter(
                $selectedOptionIds,
                static function (string $optionId) use (
                    $optionGroupIds,
                    $candidateGroupId
                ): bool {
                    return ($optionGroupIds[$optionId] ?? null) !== $candidateGroupId;
                }
            );

            $candidateOptionIds[] = $candidateId;

            $candidateOptionIds = array_values(
                array_unique($candidateOptionIds)
            );

            $criteria = new Criteria();
            $criteria->addState(self::CRITERIA_STATE);

            $mainFilters = $this->buildMainFilters(
                $partsListConfigurator,
                $productStreams,
                $candidateOptionIds
            );

            $criteria->addPostFilter(
                new AndFilter([
                    new OrFilter($mainFilters),
                ])
            );

            /*
             * Wir wollen nur wissen:
             * Gibt es mindestens EIN Produkt?
             */
            $criteria->setLimit(1);

            $result = $this->productListingRoute->load(
                $salesChannelContext
                    ->getSalesChannel()
                    ->getNavigationCategoryId(),
                $request,
                $salesChannelContext,
                $criteria
            );

            $availability[$candidateId] = $result
                    ->getResult()
                    ->count() > 0;
        }

        return $availability;
    }

    private function buildMainFilters(
        SalesChannelPartsListConfiguratorEntity $partsListConfigurator,
        ProductStreamCollection $productStreams,
        array $selectedOptionIds
    ): array {
        $mainFilters = [];

        foreach ($productStreams as $productStream) {
            $streamFilter = new ContainsFilter('streamIds', $productStream->getId());
            $propertyFilters = [];

            foreach ($partsListConfigurator->getFilters() as $filter) {
                if ($filter->getLogical()) {
                    continue;
                }

                if (!$filter->getProductStreams()->has($productStream->getId())) {
                    continue;
                }

                $whitelistIds = array_values(
                    $filter->getPropertyGroupOptions()?->getIds() ?: []
                );

                if ($filter->getFixed()) {
                    $optionIds = $whitelistIds;
                } else {
                    $optionIds = array_values(
                        array_intersect(
                            $selectedOptionIds,
                            $whitelistIds
                        )
                    );
                }

                $propertyFilters[] = $this->getPropertyFilterByIds($optionIds);
            }

            if (in_array('optional', $productStream->getTranslation('flags') ?? [], true)) {
                $mainFilters[] = new AndFilter([
                    $streamFilter,
                    new OrFilter([
                        new AndFilter($propertyFilters),
                        new EqualsFilter('parentId', null),
                    ]),
                ]);
            } else {
                $propertyFilters[] = $streamFilter;

                $mainFilters[] = new AndFilter($propertyFilters);
            }
        }

        return $mainFilters;
    }

    private function getPropertyFilterByIds(array $ids): AndFilter
    {
        if (empty($ids)) {
            return new AndFilter([]);
        }

        $grouped = $this->connection->fetchAllAssociative(
            <<<SQL
SELECT
    LOWER(HEX(property_group_id)) AS property_group_id,
    LOWER(HEX(id)) AS id
FROM property_group_option
WHERE id IN (:ids)
SQL,
            [
                'ids' => Uuid::fromHexToBytesList($ids),
            ],
            [
                'ids' => ArrayParameterType::BINARY,
            ]
        );

        $grouped = FetchModeHelper::group(
            $grouped,
            static fn (array $row): string => (string) $row['id']
        );

        $filters = [];

        foreach ($grouped as $options) {
            $filters[] = new OrFilter([
                new EqualsAnyFilter('product.optionIds', $options),
                new EqualsAnyFilter('product.propertyIds', $options),
            ]);
        }

        return new AndFilter($filters);
    }

    public function getErrorMessage(Request $request, SalesChannelContext $salesChannelContext): ?string
    {
        $partsListConfiguratorId = $request->attributes->get('partsListConfiguratorId');
        if (!$partsListConfiguratorId) {
            throw RoutingException::missingRequestParameter('partsListConfiguratorId');
        }

        $criteria = new Criteria();
        $result = $this->partsListConfiguratorDetailRoute->load($partsListConfiguratorId, $request, $salesChannelContext, $criteria);

        return $result->getPartsListConfigurator()->getTranslation('errorMessage');
    }

    public function load(
        Request $request,
        SalesChannelContext $salesChannelContext,
        array $loadingOptions = []
    ): PartsListConfiguratorPage
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
        $partsListCalculator = $this->getPartsListCalculatorByName($partsListConfigurator->getCalculatorName());

        $productStreams = new ProductStreamCollection();
        $options = new PropertyGroupOptionCollection();
        foreach ($partsListConfigurator->getFilters() as $filter) {
            $productStreams->merge($filter->getProductStreams());
            $options->merge($filter->getPropertyGroupOptions());
        }

        $mainFilters = [];
        foreach ($productStreams as $productStream) {
            $productStreamTechnicalName = $partsListConfigurator->getMappingName($productStream->getId());
            if ($productStreamTechnicalName) {
                $productStream->addTranslated('flags', $partsListCalculator->getFlags($productStreamTechnicalName));
            }

            $streamFilter = new ContainsFilter('streamIds', $productStream->getId());
            $propertyFilters = [];

            foreach ($partsListConfigurator->getFilters() as $filter) {
                if ($filter->getLogical()) {
                    /*if ($filter->getProductStreams()->has($productStream->getId())) {
                        continue;
                    }*/

                    if ($filter->getLogicalConfigurator()) {
                        continue;
                    }

                    if ($partsListCalculator->getName() === CoreCalculator::NAME) {
                        $this->logger->warning("Logical filter not allowed here.", [
                            'partsListConfiguratorId' => $partsListConfiguratorId,
                            'filterId' => $filter->getId(),
                        ]);

                        $partsListConfigurator->getFilters()->remove($filter->getId());
                        continue;
                    }

                    $groupId = $filter->getPropertyGroupOptions()?->first()?->getGroupId();
                    if (!$groupId) {
                        continue;
                    }

                    // Ein logischer Filter sollte nur eine Gruppe haben,
                    // weil der logische Konfigurator des Filters anhand des technischen Namens der Gruppe geladen wird
                    $filter->setLogicalConfigurator($partsListCalculator->getLogicalConfigurator(
                        $request,
                        $salesChannelContext,
                        $partsListConfigurator,
                        $partsListConfigurator->getMappingName($groupId)
                    ));

                    continue;
                }

                $optionIds = array_values($filter->getPropertyGroupOptions()?->getIds() ?: []);

                if ($filter->getProductStreams()->has($productStream->getId())) {
                    $propertyFilters[] = $this->getPropertyFilter($request, $optionIds, 'options', $filter->getFixed());
                }
            }

            if (in_array('optional', $productStream->getTranslation('flags') ?? [])) {
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

        $this->enrichPartsList(
            $partsListConfigurator,
            $partsListCalculator,
            $partsList,
            $productStreams,
            $options
        );

        // Parent ID entfernen, weil die Eingaben keine Variantenwechsel benötigen
        if (in_array(self::OPT_NO_PARENT, $loadingOptions)) {
            $partsListCalculator->removeParentIds($partsList);
        }

        if (in_array(self::OPT_CALCULATE, $loadingOptions)) {
            $partsListCalculator->calculatePartsList(
                $request,
                $salesChannelContext,
                $partsListConfigurator,
                $partsList
            );
        }

        $page = $this->genericLoader->load($request, $salesChannelContext);

        /** @var PartsListConfiguratorPage $page */
        $page = PartsListConfiguratorPage::createFrom($page);
        $page->setPartsListConfigurator($partsListConfigurator);
        $page->setCmsPage($partsListConfigurator->getCmsPage());
        $page->setProducts($products);
        $page->setPartsList($partsList);
        if (in_array(self::OPT_PROXY_CART, $loadingOptions)) {
            $page->setCart($this->createProxyCart($partsListConfiguratorId, $partsList, $salesChannelContext));
        }
        $page->setCalculator($partsListCalculator);

        $this->loadMetaData($page);

        return $page;
    }

    private function createProxyCart(string $partsListConfiguratorId, PartsListCollection $partsList, SalesChannelContext $salesChannelContext): Cart
    {
        $lineItems = [];
        foreach ($partsList->filterByQuantity() as $item) {
            $lineItem = new LineItem(
                $item->getId(),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                $item->getProductId(),
                $item->getQuantity()
            );

            $lineItem->setStackable(true);
            $lineItem->setRemovable(true);

            $lineItems[] = $lineItem;
        }

        $cart = $this->cartService->getCart(
            md5($partsListConfiguratorId . $salesChannelContext->getToken()),
            $salesChannelContext
        );
        $cart->setSource('moorl_pl');
        $cart->setLineItems(new LineItemCollection($lineItems));
        $cart = $this->cartService->recalculate($cart, $salesChannelContext);

        return $cart;
    }

    private function enrichPartsList(
        SalesChannelPartsListConfiguratorEntity $partsListConfigurator,
        PartsListCalculatorInterface $partsListCalculator,
        PartsListCollection $partsList,
        ProductStreamCollection $productStreams,
        PropertyGroupOptionCollection $options
    ): void
    {
        foreach ($productStreams as $productStream) {
            $productStreamTechnicalName = $partsListConfigurator->getMappingName($productStream->getId());
            if (!$productStreamTechnicalName) {
                continue;
            }

            foreach ($partsList->filterByProductStreamIds([$productStream->getId()]) as $item) {
                $item->addProductStream($productStreamTechnicalName);
            }
        }

        foreach ($options as $option) {
            foreach ($partsList as $item) {
                if (!$this->optionOrPropertyMatch($item, $option)) {
                    continue;
                }

                $groupTechnicalName = $partsListConfigurator->getMappingName($option->getGroupId());
                if ($groupTechnicalName) {
                    $item->addGroup($groupTechnicalName);
                    $customFields = $option->getTranslation('customFields');
                    if ($partsListCalculator->isCalcX($groupTechnicalName)) {
                        if (!empty($customFields['moorl_pl_calc_x_value'])) {
                            $item->setCalcX((int) $customFields['moorl_pl_calc_x_value']);
                        } else {
                            $item->setCalcX((int) $option->getTranslation('name'));
                        }
                    }
                    if ($partsListCalculator->isCalcY($groupTechnicalName)) {
                        if (!empty($customFields['moorl_pl_calc_y_value'])) {
                            $item->setCalcX((int) $customFields['moorl_pl_calc_y_value']);
                        } else {
                            $item->setCalcY((int) $option->getTranslation('name'));
                        }
                    }
                    if ($partsListCalculator->isCalcZ($groupTechnicalName)) {
                        if (!empty($customFields['moorl_pl_calc_z_value'])) {
                            $item->setCalcX((int) $customFields['moorl_pl_calc_z_value']);
                        } else {
                            $item->setCalcZ((int) $option->getTranslation('name'));
                        }
                    }
                }

                $optionTechnicalName = $partsListConfigurator->getMappingName($option->getId());
                if ($optionTechnicalName) {
                    $item->addOption($optionTechnicalName);
                }
            }
        }
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

    private function getPropertyFilter(
        Request $request,
        array $whitelistIds = [],
        string $prop = 'tag',
        bool $fixed = false
    ): AndFilter {
        if ($fixed) {
            $ids = $whitelistIds;
        } else {
            $ids = array_intersect(
                $this->getPropIds($request, $prop),
                $whitelistIds
            );
        }

        return $this->getPropertyFilterByIds(array_values($ids));
    }

    private function getPartsListCalculatorByName(string $name): PartsListCalculatorInterface
    {
        foreach ($this->partsListCalculators as $partsListCalculator) {
            if ($partsListCalculator->getName() === $name) {
                return $partsListCalculator;
            }
        }

        throw new \RuntimeException(sprintf('Unknown parts list calculator named "%s".', $name));
    }

    private function getPropIds(Request $request, string $prop = "tag", ?array $defaultIds = null): array
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

    private function optionOrPropertyMatch(PartsListEntity $item, PropertyGroupOptionEntity $option): bool
    {
        return (
            ($item->getProduct()->getOptionIds() && in_array($option->getId(), $item->getProduct()->getOptionIds())) ||
            ($item->getProduct()->getPropertyIds() && in_array($option->getId(), $item->getProduct()->getPropertyIds()))
        );
    }
}
