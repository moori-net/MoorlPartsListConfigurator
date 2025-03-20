<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Cms;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Struct\Struct;

class FenceConfiguratorListingStruct extends Struct
{
    /**
     * @var EntitySearchResult|null
     */
    protected $listing;

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
        return 'cms_look_listing';
    }
}
