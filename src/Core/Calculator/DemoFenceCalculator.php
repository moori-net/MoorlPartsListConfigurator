<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use MoorlFoundation\Core\Content\PartsList\PartsListEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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

    public function calculatePartsList(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        PartsListCollection $partsList,
        ProductCollection $products
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

        // Alle verfügbaren Produkt-Streams laden
        $productStreams = new ProductStreamCollection();
        foreach ($partsListConfigurator->getFilters() as $filter) {
            $productStreams->merge($filter->getProductStreams());
        }

        // Speichere die lesbaren technischen Namen der Produkt-Streams
        foreach ($productStreams as $productStream) {
            $productStreamTechnicalName = $productStream->getTranslation('customFields')['moorl_pl_name'] ?? null;
            if ($productStreamTechnicalName) {
                foreach ($partsList->filterByProductStreamIds([$productStream->getId()]) as $item) {
                    $item->addProductStream($productStreamTechnicalName);
                }
            }
        }

        // Alle Optionen laden
        $criteria = new Criteria();
        $criteria->addAssociation('options');
        $propertyGroupRepository = $this->partsListService->getRepository(PropertyGroupDefinition::ENTITY_NAME);
        /** @var PropertyGroupCollection $propertyGroups */
        $propertyGroups = $propertyGroupRepository->search($criteria, $salesChannelContext->getContext())->getEntities();

        // Speichere die lesbaren technischen Namen der Optionen
        foreach ($partsList as $item) {
            foreach ($propertyGroups as $propertyGroup) {
                foreach ($propertyGroup->getOptions() as $option) {
                    if (!$this->optionOrPropertyMatch($item, $option)) {
                        continue;
                    }

                    $groupTechnicalName = $propertyGroup->getTranslation('customFields')['moorl_pl_name'] ?? null;
                    $optionTechnicalName = $option->getTranslation('customFields')['moorl_pl_name'] ?? null;
                    $item->addGroup($groupTechnicalName);
                    $item->addOption($optionTechnicalName);

                    // Produkte mit einer Längen-Eigenschaft sollen den Wert der Länge für eine spätere Berechnung erhalten
                    if ($groupTechnicalName === 'LENGTH') {
                        $item->setCalcX((int) $option->getTranslation('name'));
                    }
                }
            }
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
                        $element['name']
                    );

                    // Starte Berechnung für Seite
                    $this->calculatePartsListForSide($request, $partsList, $element['name']);
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
