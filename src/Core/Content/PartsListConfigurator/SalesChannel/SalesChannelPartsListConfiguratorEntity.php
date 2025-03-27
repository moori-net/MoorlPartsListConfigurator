<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\MoorlPartsListConfigurator;
use Shopware\Core\Content\ProductStream\ProductStreamCollection;

class SalesChannelPartsListConfiguratorEntity extends PartsListConfiguratorEntity
{
    protected array $currentOptionIds = [];
    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId ?: MoorlPartsListConfigurator::CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID;
    }

    public function getCurrentOptionIds(): array
    {
        return $this->currentOptionIds;
    }

    public function setCurrentOptionIds(array $currentOptionIds): void
    {
        $this->currentOptionIds = $currentOptionIds;
    }

    public function mergeCurrentOptionIds(array $currentOptionIds): void
    {
        $this->currentOptionIds = array_merge($this->currentOptionIds, $currentOptionIds);
    }

    public function findOptionTechnicalName(string $groupTechnicalName): ?string
    {
        foreach ($this->getFilters() as $filter) {
            foreach ($filter->getOptions() as $option) {
                if (!in_array($option->getId(), $this->currentOptionIds)) {
                    continue;
                }

                if ($option->getGroup()->getTranslation('customFields')['moorl_pl_name'] === $groupTechnicalName) {
                    return $option->getTranslation('customFields')['moorl_pl_name'];
                }
            }
        }

        return null;
    }

    public function getAccessoryProductStreamIds(): array
    {
        $productStreamIds = [];

        $productStreams = new ProductStreamCollection();
        foreach ($this->getFilters() as $filter) {
            $productStreams->merge($filter->getProductStreams());
        }

        foreach ($productStreams as $productStream) {
            if ($productStream->getTranslation('customFields')['moorl_pl_optional'] ?? false) {
                $productStreamIds[] = $productStream->getId();
            }
        }

        return array_unique($productStreamIds);
    }
}
