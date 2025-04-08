<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\ExtractedDefinition;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMediaGalleryMediaCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMultiEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldThingCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl';
    final public const PROPERTY_NAME = 'partsListConfigurator';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PartsListConfiguratorCollection::class;
    }

    public function getEntityClass(): string
    {
        return PartsListConfiguratorEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false,
            'calculator' => 'demo-fence',
            'type' => 'calculator',
        ];
    }

    protected function defineFields(): FieldCollection
    {
        ExtractedDefinition::addVersionDefinition(self::class);

        return new FieldCollection(array_merge(
            FieldEntityCollection::getFieldItems(
                localClass: self::class,
                translationReferenceClass: PartsListConfiguratorTranslationDefinition::class
            ),
            [
                (new StringField('type', 'type'))->addFlags(new Required()),
                (new StringField('calculator', 'calculator'))
            ],
            FieldThingCollection::getFieldItems(media: false),
            FieldMediaGalleryMediaCollection::getFieldItems(
                localClass: self::class,
                mediaReferenceClass: PartsListConfiguratorMediaDefinition::class
            ),
            FieldMultiEntityCollection::getOneToManyFieldItems(
                localClass: self::class,
                referenceClasses: [PartsListConfiguratorFilterDefinition::class]
            ),
        ));
    }
}
