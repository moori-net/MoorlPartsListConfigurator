<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\Events\PartsListConfiguratorListingCriteriaEvent;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\Events\PartsListConfiguratorListingResultEvent;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\Events\PartsListConfiguratorSearchCriteriaEvent;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\Events\PartsListConfiguratorSearchResultEvent;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\Events\PartsListConfiguratorSuggestCriteriaEvent;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\Events\PartsListConfiguratorSuggestResultEvent;
use MoorlFoundation\Core\System\EntityListingExtension;
use MoorlFoundation\Core\System\EntityListingInterface;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class PartsListConfiguratorListing extends EntityListingExtension implements EntityListingInterface
{
    public function getEntityName(): string
    {
        return PartsListConfiguratorDefinition::ENTITY_NAME;
    }

    public function getTitle(): string
    {
        return 'parts-list-configurator-listing';
    }

    public function getSnippet(): ?string
    {
        return 'moorl-parts-list-configurator.partsListConfigurators';
    }

    public function getElementConfig(): array
    {
        if ($this->isSearch() && $this->systemConfigService->get('MoorlPartsListConfigurator.config.searchConfigActive')) {
            return $this->systemConfigService->get('MoorlPartsListConfigurator.config.searchConfig') ?: parent::getElementConfig();
        } elseif ($this->isSuggest() && $this->systemConfigService->get('MoorlPartsListConfigurator.config.suggestConfigActive')) {
            return $this->systemConfigService->get('MoorlPartsListConfigurator.config.suggestConfig') ?: parent::getElementConfig();
        }

        return parent::getElementConfig();
    }

    public function isActive(): bool
    {
        if ($this->isSearch()) {
            return $this->systemConfigService->get('MoorlPartsListConfigurator.config.searchActive') ? true : false;
        } elseif ($this->isSuggest()) {
            return $this->systemConfigService->get('MoorlPartsListConfigurator.config.suggestActive') ? true : false;
        }

        return true;
    }

    public function getLimit(): int
    {
        if ($this->isSearch()) {
            return $this->systemConfigService->get('MoorlPartsListConfigurator.config.searchLimit') ?: 12;
        } elseif ($this->isSuggest()) {
            return $this->systemConfigService->get('MoorlPartsListConfigurator.config.suggestLimit') ?: 6;
        }

        return 1;
    }

    public function processCriteria(Criteria $criteria): void
    {
        $criteria->addAssociation('cover');
        $criteria->addFilter(new PartsListConfiguratorAvailableFilter($this->salesChannelContext));

        if ($this->event instanceof ProductSuggestResultEvent) {
            $eventClass = PartsListConfiguratorSuggestCriteriaEvent::class;
        } elseif ($this->event instanceof ProductSearchResultEvent) {
            $eventClass = PartsListConfiguratorSearchCriteriaEvent::class;
        } elseif ($this->isWidget()) {
            $eventClass = PartsListConfiguratorSearchCriteriaEvent::class;
        } else {
            $eventClass = PartsListConfiguratorListingCriteriaEvent::class;
        }

        $this->eventDispatcher->dispatch(
            new $eventClass($this->request, $criteria, $this->salesChannelContext)
        );
    }

    public function processSearchResult(ProductListingResult $searchResult): void
    {
        if ($this->event instanceof ProductSuggestResultEvent) {
            $eventClass = PartsListConfiguratorSuggestResultEvent::class;
        } elseif ($this->event instanceof ProductSearchResultEvent) {
            $eventClass = PartsListConfiguratorSearchResultEvent::class;
        } elseif ($this->isWidget()) {
            $eventClass = PartsListConfiguratorSearchResultEvent::class;
        } else {
            $eventClass = PartsListConfiguratorListingResultEvent::class;
        }

        $this->eventDispatcher->dispatch(
            new $eventClass($this->request, $searchResult, $this->salesChannelContext)
        );
    }
}
