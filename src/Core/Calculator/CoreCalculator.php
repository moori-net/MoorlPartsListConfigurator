<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Calculator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class CoreCalculator extends PartsListCalculatorExtension implements PartsListCalculatorInterface
{
    public const NAME = 'core';

    public function __construct()
    {}

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMapping(): array
    {
        return [];
    }

    public function calculatePartsList(
        Request $request,
        SalesChannelContext $salesChannelContext,
        PartsListConfiguratorEntity $partsListConfigurator,
        PartsListCollection $partsList
    ): PartsListCollection
    {
        $this->removeParentIds($partsList);

        // Setze Mengen anhand des Requests
        $this->setQuantityFromRequest(
            $request,
            $partsList,
            self::NAME,
        );

        return $partsList;
    }
}
