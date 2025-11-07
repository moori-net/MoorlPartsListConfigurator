<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface PartsListCalculatorInterface
{
    public function getFlags(string $n): array;
    public function isCalcX(string $name): bool;
    public function isCalcY(string $name): bool;
    public function isCalcZ(string $name): bool;
    public function isHidden(string $name): bool;
    public function isOptional(string $name): bool;
    public function getName(): string;
    public function getMapping(): array;
    public function getPropertyGroupConfig(): array;
    public function getLogicalConfigurator(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        ?string $groupTechnicalName = null
    );
    public function calculatePartsList(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        PartsListCollection $partsList
    ): PartsListCollection;
    public function removeParentIds(PartsListCollection $partsList): void;
}
