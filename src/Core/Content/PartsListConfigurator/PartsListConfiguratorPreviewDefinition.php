<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\ExtractedDefinition;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMediaCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMultiEntityCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorPreviewDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_preview';
    final public const PROPERTY_NAME = 'preview';
    final public const EXTENSION_COLLECTION_NAME = 'moorlPlPreviews';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PartsListConfiguratorPreviewCollection::class;
    }

    public function getEntityClass(): string
    {
        return PartsListConfiguratorPreviewEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PartsListConfiguratorDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        ExtractedDefinition::addVersionDefinition(self::class);

        return new FieldCollection(array_merge(
            FieldEntityCollection::getFieldItems(
                localClass: self::class
            ),
            FieldMediaCollection::getFieldItems(),
            FieldMultiEntityCollection::getManyToOneFieldItems(
                references: [
                    [
                        PartsListConfiguratorDefinition::class,
                        [new Required()],
                        []
                    ]
                ],
            ),
            FieldMultiEntityCollection::getManyToManyFieldItems(
                localClass: self::class,
                references: [
                    [
                        PropertyGroupOptionDefinition::class,
                        PartsListConfiguratorPreviewOptionDefinition::class,
                        []
                    ],
                ],
            ),
        ));
    }
}
