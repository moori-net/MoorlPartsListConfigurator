<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorEntity;
use Moorl\FenceConfigurator\MoorlFenceConfigurator;

class SalesChannelFenceConfiguratorEntity extends FenceConfiguratorEntity
{
    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId ?: MoorlFenceConfigurator::CMS_PAGE_FENCE_CONFIGURATOR_DEFAULT_ID;
    }
}
