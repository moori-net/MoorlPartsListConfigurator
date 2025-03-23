<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Moorl\PartsListConfigurator\MoorlPartsListConfigurator;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingBaseTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingMetaTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingPageTrait;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListConfiguratorEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;
    use EntityThingMetaTrait;
    use EntityThingPageTrait;
    use EntityThingBaseTrait;

    protected bool $active = false;
    protected ?string $coverId = null;
    protected ?string $teaser = null;
    protected string $calculator;
    protected ?PartsListConfiguratorProductStreamCollection $productStreams = null;
    protected ?PartsListConfiguratorFilterCollection $filters = null;
    protected ?PartsListConfiguratorMediaCollection $media = null;
    protected ?PartsListConfiguratorMediaEntity $cover = null;

    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId ?: MoorlPartsListConfigurator::CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID;
    }
}
