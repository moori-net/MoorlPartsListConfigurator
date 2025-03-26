<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface PartsListCalculatorInterface
{
    public function getName(): string;
    public function getExpectedPropertyGroups(): array;
    public function getPropertyGroupConfig(): array;
    public function getExpectedPropertyGroupOptions(): array;
    public function getLogicalConfigurator(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        ?string $groupTechnicalName = null
    );
    public function calculate(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator
    ): PartsListCollection;

    public function calculatePartsList(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        PartsListCollection $partsList,
        ProductCollection $products
    ): PartsListCollection;
}
