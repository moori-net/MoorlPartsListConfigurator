<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMediaGalleryMediaCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorMediaDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_media';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PartsListConfiguratorMediaCollection::class;
    }

    public function getEntityClass(): string
    {
        return PartsListConfiguratorMediaEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PartsListConfiguratorDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(FieldMediaGalleryMediaCollection::getMediaFieldItems(
            referenceClass: PartsListConfiguratorDefinition::class
        ));
    }
}
