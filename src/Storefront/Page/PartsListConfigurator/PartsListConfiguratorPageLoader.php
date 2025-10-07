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

        $calculator = $this->getPartsListCalculatorByName($partsListConfigurator->getCalculatorName());

        $productStreams = new ProductStreamCollection();
        $options = new PropertyGroupOptionCollection();
        foreach ($partsListConfigurator->getFilters() as $filter) {
            $productStreams->merge($filter->getProductStreams());
            $options->merge($filter->getPropertyGroupOptions());
        }

        $mainFilters = [];
        foreach ($productStreams as $productStream) {
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

                    if ($calculator->getName() === CoreCalculator::NAME) {
                        $this->logger->warning("Logical filter not allowed here.", [
                            'partsListConfiguratorId' => $partsListConfiguratorId,
                            'filterId' => $filter->getId(),
                        ]);

                        $partsListConfigurator->getFilters()->remove($filter->getId());
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

                $optionIds = array_values($filter->getPropertyGroupOptions()?->getIds() ?: []);

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

        $this->enrichPartsList($partsList, $productStreams, $options);

        // Parent ID entfernen, weil die Eingaben keine Variantenwechsel benötigen
        if (in_array(self::OPT_NO_PARENT, $loadingOptions)) {
            $calculator->removeParentIds($partsList);
        }

        if (in_array(self::OPT_CALCULATE, $loadingOptions)) {
            $calculator->calculatePartsList(
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
        $page->setCalculator($calculator);

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
        PartsListCollection $partsList,
        ProductStreamCollection $productStreams,
        PropertyGroupOptionCollection $options
    ): void
    {
        foreach ($productStreams as $productStream) {
            $productStreamTechnicalName = $productStream->getTranslation('customFields')['moorl_pl_name'] ?? null;
            if ($productStreamTechnicalName) {
                foreach ($partsList->filterByProductStreamIds([$productStream->getId()]) as $item) {
                    $item->addProductStream($productStreamTechnicalName);
                }
            }
        }

        foreach ($options as $option) {
            foreach ($partsList as $item) {
                if (!$this->optionOrPropertyMatch($item, $option)) {
                    continue;
                }

                $groupTechnicalName = $option->getGroup()->getTranslation('customFields')['moorl_pl_name'] ?? null;
                $item->addGroup($groupTechnicalName);

                $optionTechnicalName = $option->getTranslation('customFields')['moorl_pl_name'] ?? null;
                $item->addOption($optionTechnicalName);

                $groupCalculators = $option->getGroup()->getTranslation('customFields')['moorl_pl_calculators'] ?? [];
                if (in_array("x", $groupCalculators)) {
                    $item->setCalcX((int) $option->getTranslation('name'));
                }
                if (in_array("y", $groupCalculators)) {
                    $item->setCalcY((int) $option->getTranslation('name'));
                }
                if (in_array("z", $groupCalculators)) {
                    $item->setCalcZ((int) $option->getTranslation('name'));
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
