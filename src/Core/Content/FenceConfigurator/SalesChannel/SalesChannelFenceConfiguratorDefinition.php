<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SalesChannelFenceConfiguratorDefinition extends FenceConfiguratorDefinition implements SalesChannelDefinitionInterface
{
    public function getEntityClass(): string
    {
        return SalesChannelFenceConfiguratorEntity::class;
    }

    public function processCriteria(Criteria $criteria, SalesChannelContext $context): void
    {
        $criteria->addAssociation('media');
        $criteria->addAssociation('cover.media');

        if (!$this->hasAvailableFilter($criteria)) {
            $criteria->addFilter(
                new FenceConfiguratorAvailableFilter($context)
            );
        }
    }

    private function hasAvailableFilter(Criteria $criteria): bool
    {
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof FenceConfiguratorAvailableFilter) {
                return true;
            }
        }

        return false;
    }
}
