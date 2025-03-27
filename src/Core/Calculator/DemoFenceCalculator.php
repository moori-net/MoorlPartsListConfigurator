<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DemoFenceCalculator extends PartsListCalculatorExtension implements PartsListCalculatorInterface
{
    public function __construct(private readonly PartsListService $partsListService)
    {
    }

    public function getName(): string
    {
        return 'demo-fence';
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

        // Für die Berechnung werden erst die großen Produkte verwendet und später mit kleinen Produkten ergänzt
        $partsList->sortByCalcX();

        // Starte Berechnung
        foreach ($logicalConfigurators as $logicalConfigurator) {
            if (isset($logicalConfigurator['elements'])) {
                foreach ($logicalConfigurator['elements'] as $element) {
                    // Setze Mengen anhand des Requests
                    $this->setQuantityFromRequest(
                        $request,
                        $partsList->filterByProductStream('LAYOUT_ACCESSORIES'),
                        $element
                    );

                    // Starte Berechnung für Seite
                    $this->calculatePartsListForSide($request, $partsList, $element);
                }
            }
        }

        // Ermittlung der Pfosten
        $cornerPost = $partsList->filterByOption('PARTS_LIST_POST_TYPE_CORNER')->first();
        $sidePost = $partsList->filterByOption('PARTS_LIST_POST_TYPE_SIDE')->first();

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

        $length = (int) $request->query->get($parameterName);
        if (!$length) {
            throw RoutingException::missingRequestParameter($parameterName);
        }

        $this->partsListService->debug(sprintf("Got length %d for side %s", $length, $parameterName));

        // Eckpfosten pro Seite hinzufügen
        $cornerPost = $partsList->filterByOption('PARTS_LIST_POST_TYPE_CORNER')->first();
        $cornerPost->setQuantity($cornerPost->getQuantity() + 1);

        // Fest definiertes Zubehör wird von der Gesamtlänge abgezogen,
        // die übrige Gesamtlänge wird für die Berechnung der Zaunmatten verwendet
        foreach ($partsList->filterByProductStream('LAYOUT_ACCESSORIES') as $item) {
            $this->partsListService->debug(sprintf(
                "%s have a length of %d and is given %d-times - the remaining length for %s is %d",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $item->getTemporaryQuantity(),
                $sideName,
                $length
            ));

            $length = $length - $item->getTemporaryQuantity() * $item->getCalcX();
            if ($length < 0) {
                throw new \Exception(sprintf(
                    "Error, the length of %d * %s is to big",
                    $item->getTemporaryQuantity(),
                    $item->getCalcX()
                ));
            }

            // Zurücksetzen und für die Berechnung der folgenden Seite vorbereiten
            $item->setTemporaryQuantity(0);
        }

        // Verwende die übrige Länge, um die Zaunmatten einzufügen
        foreach ($partsList->filterByProductStream("FENCES") as $item) {
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

        $this->partsListService->debug(sprintf("the remaining length for %s is %d", $sideName, $length));
    }
}
