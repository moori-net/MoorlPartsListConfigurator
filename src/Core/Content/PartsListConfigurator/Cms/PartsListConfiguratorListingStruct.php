<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\Cms;

use MoorlFoundation\Core\Content\Cms\SalesChannel\Struct\ListingStruct;

class PartsListConfiguratorListingStruct extends ListingStruct
{
    public function getApiAlias(): string
    {
        return 'cms_parts_list_configurator_listing';
    }
}
