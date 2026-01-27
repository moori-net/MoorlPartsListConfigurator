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
    private float $overhang = 0.00; // Füge Zaunmatten für den Überhang hinzu
    private float $length = 0.00; // Gesamtlänge Zaun

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

        // Starte Berechnung
        foreach ($logicalConfigurators as $logicalConfigurator) {
            if (isset($logicalConfigurator['elements'])) {
                foreach ($logicalConfigurator['elements'] as $element) {
                    // Starte Berechnung für Seite
                    $this->calculatePartsListForSide($request, $partsList, $element);
                }
            }
        }

        // Fest definiertes Zubehör wird von der Gesamtlänge abgezogen,
        // die übrige Gesamtlänge wird für die Berechnung der Zaunmatten verwendet
        foreach ($partsList->filterByProductStream('LAYOUT_ACCESSORIES') as $item) {
            if ($item->getTemporaryQuantity() === 0) {
                continue;
            }

            $this->partsListService->debug(sprintf(
                "%s have a length of %d and is given %d-times",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $item->getTemporaryQuantity(),

            ));

            $this->length = $this->length - $item->getTemporaryQuantity() * $item->getCalcX();
            if ($this->length < 0) {
                throw PartsListCalculatorException::calculationAborted('length');
            }
        }

        // Verwende die übrige Länge, um die Zaunmatten einzufügen
        foreach ($partsList->filterByProductStream("FENCES") as $item) {
            $quantity = (int) floor($this->length / $item->getCalcX());
            if ($quantity === 0) {
                continue;
            }

            $this->length = $this->length - ($quantity * $item->getCalcX());

            $this->partsListService->debug(sprintf(
                "%s have a length of %d and is given %d-times",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $quantity
            ));

            $item->setQuantity($item->getQuantity() + $quantity);
        }

        if ($this->length > 0) {
            $this->partsListService->debug(sprintf("Overhang is %d", $this->length));
            $this->overhang += $this->length;
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

        // Überhang berücksichtigen
        if ($this->overhang > 0) {
            $this->partsListService->debug(sprintf("Overhang detected: %d", $this->overhang));

            // Mindestlänge ermitteln
            $shortest = 0;
            foreach ($partsList->filterByProductStream("FENCES") as $item) {
                if ($shortest === 0 || $item->getCalcX() < $shortest) {
                    $shortest = $item->getCalcX();
                }
            }
            if ($shortest > 0) {
                $this->overhang = ceil($this->overhang / $shortest) * $shortest;
            }

            foreach ($partsList->filterByProductStream("FENCES") as $item) {
                $quantity = (int) floor($this->overhang / $item->getCalcX());
                $this->overhang = $this->overhang - ($quantity * $item->getCalcX());
                $item->setQuantity($item->getQuantity() + $quantity);
            }
        }

        return $partsList;
    }

    private function calculatePartsListForSide(Request $request, PartsListCollection $partsList, string $sideName): void
    {
        $parameterName = sprintf("%s_length", $sideName);

        $length = (int) $request->query->get($parameterName);
        if (!$length) {
            throw RoutingException::missingRequestParameter($parameterName);
        }

        $this->partsListService->debug(sprintf("Got length %d for side %s", $length, $parameterName));

        $this->length = $this->length + $length;

        // Eckpfosten pro Seite hinzufügen
        $cornerPost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_CORNER');
        $cornerPost->setQuantity($cornerPost->getQuantity() + 1);
    }
}
