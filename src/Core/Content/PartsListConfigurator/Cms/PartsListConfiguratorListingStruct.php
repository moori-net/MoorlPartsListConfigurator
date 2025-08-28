<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\Cms;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Struct\Struct;

class PartsListConfiguratorListingStruct extends Struct
{
    protected ?EntitySearchResult $listing = null;

    public function getListing(): ?EntitySearchResult
    {
        return $this->listing;
    }

    public function setListing(EntitySearchResult $listing): void
    {
        $this->listing = $listing;
    }

    public function getApiAlias(): string
    {
        return 'cms_parts_list_configurator_listing';
    }
}
