<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DemoFenceCalculator extends PartsListCalculatorExtension implements PartsListCalculatorInterface
{
    public function __construct(
        private readonly PartsListService $partsListService
    )
    {
    }

    public function getName(): string
    {
        return 'demo-fence';
    }

    public function getExpectedPropertyGroups(): array
    {
        return [
            'PARTS_LIST_LAYOUT',
            'LENGTH'
        ];
    }

    public function getPropertyGroupConfig(): array
    {
        return [
            [
                'technicalName' => 'PARTS_LIST_LAYOUT',
                'options' => [
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_1',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number']
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_2',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number'],
                            ['name' => 'side_b', 'type' => 'number']
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_3',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number'],
                            ['name' => 'side_b', 'type' => 'number'],
                            ['name' => 'side_c', 'type' => 'number'],
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_4',
                        'name' => 'length',
                        'elements' => [
                            ['name' => 'side_a', 'type' => 'number'],
                            ['name' => 'side_b', 'type' => 'number'],
                            ['name' => 'side_c', 'type' => 'number'],
                            ['name' => 'side_d', 'type' => 'number'],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getExpectedPropertyGroupOptions(): array
    {
        return [
            'PARTS_LIST_POST_TYPE_SIDE',
            'PARTS_LIST_POST_TYPE_CORNER',
        ];
    }

    public function calculate(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator
    ): PartsListCollection
    {
        $productBuyList = new PartsListCollection();

        return $productBuyList;
    }

    public function calculatePartsList(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        PartsListCollection $partsList,
        ProductCollection $products
    ): PartsListCollection
    {
        $logicalConfigurators = [];

        foreach ($this->getPropertyGroupConfig() as $item) {
            $logicalConfigurators[$item['technicalName']] = $this->getLogicalConfigurator(
                $request,
                $salesChannelContext,
                $partsListConfigurator,
                $item['technicalName']
            );
        }

        // Alle Optionen für Breite laden
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customFields.moorl_pl_name', 'LENGTH'));
        $criteria->addAssociation('options');
        $propertyGroupRepository = $this->partsListService->getRepository(PropertyGroupDefinition::ENTITY_NAME);
        /** @var PropertyGroupEntity $propertyGroup */
        $propertyGroup = $propertyGroupRepository->search($criteria, $salesChannelContext->getContext())->first();

        // Speichere die Breite in die Stücklisten-Positionen, für die spätere Berechnung
        foreach ($partsList as $item) {
            foreach ($propertyGroup->getOptions() as $option) {
                if ($item->getProduct()->getOptionIds() && in_array($option->getId(), $item->getProduct()->getOptionIds())) {
                    $item->setCalcX((int) $option->getTranslation('name'));
                    continue 2;
                }

                if ($item->getProduct()->getPropertyIds() && in_array($option->getId(), $item->getProduct()->getPropertyIds())) {
                    $item->setCalcX((int) $option->getTranslation('name'));
                    continue 2;
                }
            }
        }

        // TODO: Eckpfosten auf -1 setzen, weil mindestens eine Seite berechnet wird

        // Setze Gruppe für Zaunmatten
        $fenceProductStreamId = $partsListConfigurator
            ->getPartsListConfiguratorProductStreams()
            ->getByTechnicalName('fences')
            ->getProductStreamId();

        foreach ($partsList->filterByProductStreamIds([$fenceProductStreamId]) as $item) {
            $item->setGroup('fences');
        }

        // Für die Berechnung werden erst die großen Produkte verwendet und später mit kleinen Produkten ergänzt
        $partsList->sortByCalcX();

        foreach ($logicalConfigurators as $logicalConfigurator) {
            if (isset($logicalConfigurator['elements'])) {
                foreach ($logicalConfigurator['elements'] as $element) {
                    $this->calculatePartsListForSide($request, $partsList, $element['name']);
                }
            }
        }

        // Optionales Zubehör hinzufügen
        $this->calculatePartsListItemQuantity($request, $partsList, "accessory", "accessories");



        return $partsList;
    }

    private function calculatePartsListForSide(Request $request, PartsListCollection $partsList, string $sideName): void
    {
        $parameterName = sprintf("%s_length", $sideName);

        $length = (int) $request->query->get($parameterName);
        if (!$length) {
            throw RoutingException::missingRequestParameter($parameterName);
        }

        $this->partsListService->debug(sprintf(
            "Got length %d for side %s",
            $length,
            $parameterName
        ));

        // TODO: Pro Seite ein Eckpfosten hinzufügen

        $this->calculatePartsListItemQuantity($request, $partsList, $sideName, "doors");

        // Fest definiertes Zubehör wird von der Gesamtlänge abgezogen,
        // die übrige Gesamtlänge wird für die Berechnung der Zaunmatten verwendet
        foreach ($partsList->filterByGroup("doors") as $item) {
            $this->partsListService->debug(sprintf(
                "%s have a length of %d and is given %d-times - the remaining length for %s is %d",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $item->getCalcY(),
                $sideName,
                $length
            ));

            $length = $length - $item->getCalcY() * $item->getCalcX();
            if ($length < 0) {
                throw new \Exception(sprintf(
                    "Error, the length of %d * %s is to big",
                    $item->getCalcY(),
                    $item->getCalcX()
                ));
            }

            // Zurücksetzen und für die Berechnung der folgenden Seite vorbereiten
            $item->setCalcY(0);
        }

        // Verwende die übrige Länge, um die Zaunmatten einzufügen
        foreach ($partsList->filterByGroup("fences") as $item) {
            $quantity = (int) floor($length / $item->getCalcX());

            $length = $length - ($quantity * $item->getCalcX());

            $this->partsListService->debug(sprintf(
                "%s have a length of %d and is given %d-times - the remaining length for %s is %d",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $quantity,
                $sideName,
                $length
            ));

            $item->setQuantity($item->getQuantity() + $quantity);
        }

        $this->partsListService->debug(sprintf(
            "the remaining length for %s is %d",
            $sideName,
            $length
        ));
    }

    private function calculatePartsListItemQuantity(Request $request, PartsListCollection $partsList, string $name, string $group): void
    {
        foreach ($partsList as $item) {
            $parameterName = sprintf(
                "%s_%s",
                $name,
                $item->getProduct()->getParentId() ?: $item->getProductId()
            );

            $itemQuantity = (int) $request->query->get($parameterName);
            dump($itemQuantity);
            if (!$itemQuantity) {
                continue;
            }

            $this->partsListService->debug(sprintf(
                "Got quantity %d for product %s",
                $itemQuantity,
                $parameterName
            ));

            $item->setQuantity($item->getQuantity() + $itemQuantity);
            // Wird nach der Berechnung der Länge für die aktuelle Seite wieder zurückgesetzt
            $item->setCalcY($item->getCalcY() + $itemQuantity);
            $item->setGroup($group);
        }
    }
}
