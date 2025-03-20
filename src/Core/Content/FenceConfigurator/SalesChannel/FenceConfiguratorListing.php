<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use MoorlFoundation\Core\System\EntityListingExtension;
use MoorlFoundation\Core\System\EntityListingInterface;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\Events\FenceConfiguratorListingCriteriaEvent;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\Events\FenceConfiguratorListingResultEvent;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\Events\FenceConfiguratorSearchCriteriaEvent;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\Events\FenceConfiguratorSearchResultEvent;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\Events\FenceConfiguratorSuggestCriteriaEvent;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel\Events\FenceConfiguratorSuggestResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class FenceConfiguratorListing extends EntityListingExtension implements EntityListingInterface
{
    public function getEntityName(): string
    {
        return FenceConfiguratorDefinition::ENTITY_NAME;
    }

    public function getTitle(): string
    {
        return 'fence-configurator-listing';
    }

    public function getSnippet(): ?string
    {
        return 'moorl-fence-configurator.fenceConfigurators';
    }

    public function getElementConfig(): array
    {
        if ($this->isSearch() && $this->systemConfigService->get('MoorlFenceConfigurator.config.searchConfigActive')) {
            return $this->systemConfigService->get('MoorlFenceConfigurator.config.searchConfig') ?: parent::getElementConfig();
        } elseif ($this->isSuggest() && $this->systemConfigService->get('MoorlFenceConfigurator.config.suggestConfigActive')) {
            return $this->systemConfigService->get('MoorlFenceConfigurator.config.suggestConfig') ?: parent::getElementConfig();
        }

        return parent::getElementConfig();
    }

    public function isActive(): bool
    {
        if ($this->isSearch()) {
            return $this->systemConfigService->get('MoorlFenceConfigurator.config.searchActive') ? true : false;
        } elseif ($this->isSuggest()) {
            return $this->systemConfigService->get('MoorlFenceConfigurator.config.suggestActive') ? true : false;
        }

        return true;
    }

    public function getLimit(): int
    {
        if ($this->isSearch()) {
            return $this->systemConfigService->get('MoorlFenceConfigurator.config.searchLimit') ?: 12;
        } elseif ($this->isSuggest()) {
            return $this->systemConfigService->get('MoorlFenceConfigurator.config.suggestLimit') ?: 6;
        }

        return 1;
    }

    public function processCriteria(Criteria $criteria): void
    {
        $criteria->addAssociation('cover');
        $criteria->addFilter(new FenceConfiguratorAvailableFilter($this->salesChannelContext));

        if ($this->event instanceof ProductSuggestResultEvent) {
            $eventClass = FenceConfiguratorSuggestCriteriaEvent::class;
        } elseif ($this->event instanceof ProductSearchResultEvent) {
            $eventClass = FenceConfiguratorSearchCriteriaEvent::class;
        } elseif ($this->isWidget()) {
            $eventClass = FenceConfiguratorSearchCriteriaEvent::class;
        } else {
            $eventClass = FenceConfiguratorListingCriteriaEvent::class;
        }

        $this->eventDispatcher->dispatch(
            new $eventClass($this->request, $criteria, $this->salesChannelContext)
        );
    }

    public function processSearchResult(ProductListingResult $searchResult): void
    {
        if ($this->event instanceof ProductSuggestResultEvent) {
            $eventClass = FenceConfiguratorSuggestResultEvent::class;
        } elseif ($this->event instanceof ProductSearchResultEvent) {
            $eventClass = FenceConfiguratorSearchResultEvent::class;
        } elseif ($this->isWidget()) {
            $eventClass = FenceConfiguratorSearchResultEvent::class;
        } else {
            $eventClass = FenceConfiguratorListingResultEvent::class;
        }

        $this->eventDispatcher->dispatch(
            new $eventClass($this->request, $searchResult, $this->salesChannelContext)
        );
    }
}
