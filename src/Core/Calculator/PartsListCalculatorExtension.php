<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
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

    public function getPropertyGroupConfig(): array
    {
        return [];
    }
}
