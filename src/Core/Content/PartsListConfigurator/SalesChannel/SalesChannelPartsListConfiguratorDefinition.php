<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SalesChannelPartsListConfiguratorDefinition extends PartsListConfiguratorDefinition implements SalesChannelDefinitionInterface
{
    public function getEntityClass(): string
    {
        return SalesChannelPartsListConfiguratorEntity::class;
    }

    public function processCriteria(Criteria $criteria, SalesChannelContext $context): void
    {
        $criteria->addAssociation('media');
        $criteria->addAssociation('cover.media');
        $criteria->addAssociation('partsListConfiguratorProductStreams');
        $criteria->addAssociation('filters.propertyGroupOptions.group');
        $criteria->addAssociation('filters.propertyGroupOptions.media');
        $criteria->addAssociation('filters.productStreams');

        if (!$this->hasAvailableFilter($criteria)) {
            $criteria->addFilter(
                new PartsListConfiguratorAvailableFilter($context)
            );
        }
    }

    private function hasAvailableFilter(Criteria $criteria): bool
    {
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof PartsListConfiguratorAvailableFilter) {
                return true;
            }
        }

        return false;
    }
}
