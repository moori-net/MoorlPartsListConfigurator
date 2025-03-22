<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\MoorlPartsListConfigurator;

class SalesChannelPartsListConfiguratorEntity extends PartsListConfiguratorEntity
{
    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId ?: MoorlPartsListConfigurator::CMS_PAGE_FENCE_CONFIGURATOR_DEFAULT_ID;
    }
}
