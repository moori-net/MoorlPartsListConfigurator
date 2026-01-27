<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DemoFenceCalculator2 extends PartsListCalculatorExtension implements PartsListCalculatorInterface
{
    private int $shortestFence = 0; // Mindestlänge Zaun

    public function __construct(private readonly PartsListService $partsListService)
    {
    }

    public function getName(): string
    {
        return 'demo-fence-2';
    }

    public function getMapping(): array
    {
        return [
            ProductStreamDefinition::ENTITY_NAME => [
                'OPTIONAL_ACCESSORIES' => ['optional'],
                'LAYOUT_ACCESSORIES' => ['optional'],
                'FENCES' => [],
            ],
            PropertyGroupDefinition::ENTITY_NAME => [
                'PARTS_LIST_LAYOUT' => [],
                'PARTS_LIST_POST_TYPE' => ['hidden'],
                'LENGTH' => ['calc-x'],
            ],
            PropertyGroupOptionDefinition::ENTITY_NAME => [
                'PARTS_LIST_LAYOUT_1' => [],
                'PARTS_LIST_LAYOUT_2' => [],
                'PARTS_LIST_LAYOUT_3' => [],
                'PARTS_LIST_LAYOUT_4' => [],
                'PARTS_LIST_POST_TYPE_CORNER' => [],
                'PARTS_LIST_POST_TYPE_SIDE' => []
            ],
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
                        'elements' => [
                            'side_a'
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_2',
                        'elements' => [
                            'side_a',
                            'side_b'
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_3',
                        'elements' => [
                            'side_a',
                            'side_b',
                            'side_c'
                        ]
                    ],
                    [
                        'technicalName' => 'PARTS_LIST_LAYOUT_4',
                        'elements' => [
                            'side_a',
                            'side_b',
                            'side_c',
                            'side_d'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function calculatePartsList(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        PartsListCollection $partsList
    ): PartsListCollection
    {
        // Logische Konfiguratoren laden
        $logicalConfigurators = [];
        foreach ($this->getPropertyGroupConfig() as $item) {
            $logicalConfigurators[$item['technicalName']] = $this->getLogicalConfigurator(
                $request,
                $salesChannelContext,
                $partsListConfigurator,
                $item['technicalName']
            );
        }

        // Setze Mengen anhand des Requests
        $this->setQuantityFromRequest(
            $request,
            $partsList->filterByProductStream('OPTIONAL_ACCESSORIES'),
            "accessory"
        );

        $this->setQuantityFromRequest(
            $request,
            $partsList->filterByProductStream('LAYOUT_ACCESSORIES'),
            "accessory"
        );

        // Für die Berechnung werden erst die großen Produkte verwendet und später mit kleinen Produkten ergänzt
        $partsList->sortByCalcX();

        // Mindestlänge pro Seite ermitteln
        foreach ($partsList->filterByProductStream("FENCES") as $item) {
            if ($this->shortestFence === 0 || $item->getCalcX() < $this->shortestFence) {
                $this->shortestFence = $item->getCalcX();
            }
        }

        // Starte Berechnung
        foreach ($logicalConfigurators as $logicalConfigurator) {
            if (isset($logicalConfigurator['elements'])) {
                foreach ($logicalConfigurator['elements'] as $element) {
                    // Starte Berechnung für Seite
                    $this->calculatePartsListForSide($request, $partsList, $element);
                }
            }
        }

        // Ermittlung der Pfosten
        $cornerPost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_CORNER');
        $sidePost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_SIDE');

        // einen Eckpfosten wieder abziehen
        $cornerPost->setQuantity($cornerPost->getQuantity() - 1);

        // einen Seitenpfosten hinzufügen
        $sidePost->setQuantity(1);

        // einen Seitenpfosten pro Zaunmatte hinzufügen
        foreach ($partsList->filterByProductStream("FENCES") as $item) {
            $sidePost->setQuantity($item->getQuantity() + $sidePost->getQuantity());
        }

        // die Anzahl der Eckpfosten wieder abziehen
        $sidePost->setQuantity($sidePost->getQuantity() - $cornerPost->getQuantity());

        return $partsList;
    }

    private function calculatePartsListForSide(Request $request, PartsListCollection $partsList, string $sideName): void
    {
        $parameterName = sprintf("%s_length", $sideName);

        $length = (int) $request->query->get($parameterName) ?: 1;
        if (!$length) {
            throw RoutingException::missingRequestParameter($parameterName);
        }

        $length = $length * 10; // cm > mm

        $this->partsListService->debug(sprintf("Got length %d for side %s", $length, $parameterName));

        // Mindestlänge anhand kleinster Zaunmatte aufrunden
        $length = ceil($length / $this->shortestFence) * $this->shortestFence;

        // Verwende die Länge, um die Zaunmatten einzufügen
        foreach ($partsList->filterByProductStream("FENCES") as $item) {
            $quantity = (int) floor($length / $item->getCalcX());
            if ($quantity === 0) {
                continue;
            }

            $length = $length - ($quantity * $item->getCalcX());

            $this->partsListService->debug(sprintf(
                "%s have a length of %d and is given %d-times",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $quantity
            ));

            $item->setQuantity($item->getQuantity() + $quantity);
        }

        // Eckpfosten pro Seite hinzufügen
        $cornerPost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_CORNER');
        $cornerPost->setQuantity($cornerPost->getQuantity() + 1);
    }
}
