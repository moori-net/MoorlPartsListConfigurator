<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use MoorlFoundation\Core\Content\PartsList\PartsListEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class PartsListCalculatorExtension
{
    public function getLogicalConfigurator(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        ?string $groupTechnicalName = null
    ): ?array
    {
        if (!$groupTechnicalName) {
            $groupTechnicalName = $request->query->get('group');
            if (!$groupTechnicalName) {
                return null;
            }
        }

        $group = current(array_filter(
            $this->getPropertyGroupConfig(),
            fn($item) => $item['technicalName'] === $groupTechnicalName
        ));
        if (!$group) {
            return null;
        }

        $optionTechnicalName = $partsListConfigurator->findOptionTechnicalName($groupTechnicalName);
        if (!$optionTechnicalName) {
            return null;
        }

        $option = current(array_filter(
            $group['options'],
            fn($item) => $item['technicalName'] === $optionTechnicalName
        ));

        $option['groupTechnicalName'] = $groupTechnicalName;
        $option['optionTechnicalName'] = $optionTechnicalName;

        return $option;
    }

    public function removeParentIds(PartsListCollection $partsList): void
    {
        foreach ($partsList as $part) {
            $part->getProduct()->setParentId(null);
        }
    }

    public function setQuantityFromRequest(Request $request, PartsListCollection $partsList, string $name): void
    {
        foreach ($partsList as $item) {
            $parameterName = sprintf(
                "%s_%s",
                $name,
                $item->getProduct()->getParentId() ?: $item->getProductId()
            );

            $itemQuantity = (int) $request->query->get($parameterName);
            $item->setTemporaryQuantity($itemQuantity);
            if (!$itemQuantity) {
                continue;
            }

            $item->setQuantity($item->getQuantity() + $itemQuantity);
        }
    }

    public function optionOrPropertyMatch(PartsListEntity $item, PropertyGroupOptionEntity $option): bool
    {
        return (
            ($item->getProduct()->getOptionIds() && in_array($option->getId(), $item->getProduct()->getOptionIds())) ||
            ($item->getProduct()->getPropertyIds() && in_array($option->getId(), $item->getProduct()->getPropertyIds()))
        );
    }

    public function getPropertyGroupConfig(): array
    {
        return [];
    }

    protected function getByOption(PartsListCollection $partsList, string $option): PartsListEntity
    {
        $entity = $partsList->filterByOption($option)->first();
        if (!$entity) {
            throw PartsListCalculatorException::missingOption($option);
        }
        return $entity;
    }

    public function isCalcX(string $name): bool
    {
        return false;
    }
    public function isCalcY(string $name): bool
    {
        return false;
    }
    public function isCalcZ(string $name): bool
    {
        return false;
    }
}
