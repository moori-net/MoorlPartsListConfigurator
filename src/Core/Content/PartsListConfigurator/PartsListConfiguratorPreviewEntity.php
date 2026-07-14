<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListConfiguratorPreviewEntity extends Entity
{
    use EntityIdTrait;

    protected string $partsListConfiguratorId = "";

    protected ?string $mediaId = null;
    protected ?MediaEntity $media = null;
    protected ?PartsListConfiguratorEntity $partsListConfigurator = null;
    protected ?PropertyGroupOptionCollection $propertyGroupOptions = null;
}
