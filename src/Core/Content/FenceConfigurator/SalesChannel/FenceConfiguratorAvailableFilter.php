<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class FenceConfiguratorAvailableFilter extends EqualsFilter
{
    public function __construct(SalesChannelContext $salesChannelContext)
    {
        parent::__construct('moorl_fc.active', true);
    }
}
