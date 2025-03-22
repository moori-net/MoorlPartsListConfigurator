<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\ProductBuyList\ProductBuyListItemCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DemoFenceCalculator implements CalculatorInterface
{
    public function getName(): string
    {
        return 'demo-fence';
    }

    public function getExpectedPropertyGroups(): array
    {
        return [
            'COLOR',
            'PARTS_LIST_LAYOUT',
            'HEIGHT',
            'LENGTH',
            'PARTS_LIST_POST_TYPE',
            'PARTS_LIST_POST_MOUNTING',
            'PARTS_LIST_POST_FLOOR_MOUNTING',
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
    ): ProductBuyListItemCollection
    {
        $productBuyList = new ProductBuyListItemCollection();

        return $productBuyList;
    }
}
