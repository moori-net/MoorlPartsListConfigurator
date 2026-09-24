<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Service\PartsListService;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
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
                'PARTS_LIST_POST_TYPE_SIDE' => [],
                'PARTS_LIST_POST_TYPE_CORNER' => ['calc-x'],
                'PARTS_LIST_POST_TYPE_FLEX_CORNER' => ['calc-y']
            ],
        ];
    }

    public function getRequiredOptions(): array
    {
        return [
            'PARTS_LIST_POST_TYPE_CORNER',
            'PARTS_LIST_POST_TYPE_SIDE',
        ];
    }

    public function getPropertyGroupConfig(): array
    {
        return [
            [
                'technicalName' => 'PARTS_LIST_LAYOUT',
            ]
        ];
    }

    public function getLogicalConfigurator(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        ?string $groupTechnicalName = null
    ): ?array {
        $groupTechnicalName ??= $request->query->get('group');
        if (!$groupTechnicalName || !current(array_filter(
            $this->getPropertyGroupConfig(),
            fn($item) => $item['technicalName'] === $groupTechnicalName
        ))) {
            return null;
        }

        $option = $this->getLogicalConfiguratorOption(
            $partsListConfigurator,
            $groupTechnicalName
        );
        if (!$option) {
            return null;
        }

        $customFields = $option->getTranslation('customFields') ?? [];
        $cornerPostQuantity = (int) ($customFields['moorl_pl_calc_x_value'] ?? 0);
        $flexCornerPostQuantity = (int) ($customFields['moorl_pl_calc_y_value'] ?? 0);
        $elements = [];

        for ($index = 0; $index <= $cornerPostQuantity + $flexCornerPostQuantity; $index++) {
            $elements[] = 'side_' . chr(ord('a') + $index);
        }

        return [
            'groupTechnicalName' => $groupTechnicalName,
            'optionId' => $option->getId(),
            'elements' => $elements,
            'cornerPostQuantity' => $cornerPostQuantity,
            'flexCornerPostQuantity' => $flexCornerPostQuantity,
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
        $sidePost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_SIDE');
        $cornerPost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_CORNER');
        $flexCornerPost = $this->getByOption($partsList, 'PARTS_LIST_POST_TYPE_FLEX_CORNER');

        // einen Eckpfosten wieder abziehen
        foreach ($logicalConfigurators as $logicalConfigurator) {
            $cornerPost->setQuantity(
                $cornerPost->getQuantity() + ($logicalConfigurator['cornerPostQuantity'] ?? 0)
            );
            $flexCornerPost->setQuantity(
                $flexCornerPost->getQuantity() + ($logicalConfigurator['flexCornerPostQuantity'] ?? 0)
            );
        }

        // einen Seitenpfosten hinzufügen
        $sidePost->setQuantity(1);

        // einen Seitenpfosten pro Zaunmatte hinzufügen
        foreach ($partsList->filterByProductStream("FENCES") as $item) {
            $sidePost->setQuantity($item->getQuantity() + $sidePost->getQuantity());
        }

        // einen Seitenpfosten pro Tor abziehen
        foreach ($partsList->filterByProductStream('LAYOUT_ACCESSORIES') as $item) {
            if ($item->getTemporaryQuantity() === 0) {
                continue;
            }

            $sidePost->setQuantity($sidePost->getQuantity() - $item->getTemporaryQuantity());
        }

        // die Anzahl der Eckpfosten wieder abziehen
        $sidePost->setQuantity(
            $sidePost->getQuantity() - $cornerPost->getQuantity() - $flexCornerPost->getQuantity()
        );

        return $partsList;
    }

    private function calculatePartsListForSide(Request $request, PartsListCollection $partsList, string $sideName): void
    {
        $parameterName = sprintf("%s_length", $sideName);

        $length = (float) $request->query->get($parameterName) ?: 1.0;
        $length = $length * 1000; // m > mm

        $this->partsListService->debug(sprintf("Got length %d mm for side %s", $length, $parameterName));

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
                "%s have a length of %d mm and is given %d-times",
                $item->getProduct()->getTranslation('name'),
                $item->getCalcX(),
                $quantity
            ));

            $item->setQuantity($item->getQuantity() + $quantity);
        }

        // Eckpfosten pro Seite hinzufügen
    }

    private function getLogicalConfiguratorOption(
        PartsListConfiguratorEntity $partsListConfigurator,
        string $groupTechnicalName
    ): ?PropertyGroupOptionEntity {
        $groupId = $partsListConfigurator->getMappingValue($groupTechnicalName);
        if (!$groupId || !method_exists($partsListConfigurator, 'getCurrentOptionIds')) {
            return null;
        }

        $selectedOptionIds = $partsListConfigurator->getCurrentOptionIds();
        foreach ($partsListConfigurator->getFilters() ?? [] as $filter) {
            foreach ($filter->getPropertyGroupOptions() ?? [] as $option) {
                if (
                    $option->getGroupId() === $groupId
                    && in_array($option->getId(), $selectedOptionIds, true)
                ) {
                    return $option;
                }
            }
        }

        return null;
    }
}
