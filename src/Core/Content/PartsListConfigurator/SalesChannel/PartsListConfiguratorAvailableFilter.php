<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PartsListConfiguratorAvailableFilter extends EqualsFilter
{
    public function __construct(SalesChannelContext $salesChannelContext)
    {
        parent::__construct('moorl_pl.active', true);
    }
}
