<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\ProductBuyList\ProductBuyListItemCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface CalculatorInterface
{
    public function getName(): string;
    public function getExpectedPropertyGroups(): array;
    public function getPropertyGroupConfig(): array;
    public function getExpectedPropertyGroupOptions(): array;
    public function calculate(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator
    ): ProductBuyListItemCollection;
}
