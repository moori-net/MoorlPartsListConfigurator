<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMultiEntityCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\VueComponent;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorFilterDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_filter';
    final public const PROPERTY_NAME = 'filter';
    final public const EXTENSION_COLLECTION_NAME = 'moorlPlFilters';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PartsListConfiguratorFilterCollection::class;
    }

    public function getEntityClass(): string
    {
        return PartsListConfiguratorFilterEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PartsListConfiguratorDefinition::class;
    }

    public function getDefaults(): array
    {
        return [
            'position' => 0,
            'fixed' => false,
            'logical' => false,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(array_merge(
            FieldEntityCollection::getFieldItems(
                localClass: self::class
            ),
            [
                (new BoolField('fixed', 'fixed'))->addFlags(new EditField(EditField::SWITCH)),
                (new BoolField('logical', 'logical'))->addFlags(new EditField(EditField::SWITCH)),
                (new IntField('position', 'position'))->addFlags(new ApiAware(), new EditField(EditField::NUMBER)),
                (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new EditField(EditField::TEXT)),
            ],
            FieldMultiEntityCollection::getManyToOneFieldItems(
                references: [
                    [
                        PartsListConfiguratorDefinition::class,
                        [new Required()],
                        [new CascadeDelete()]
                    ]
                ],
            ),
            FieldMultiEntityCollection::getManyToManyFieldItems(
                localClass: self::class,
                references: [
                    [
                        PropertyGroupOptionDefinition::class,
                        PartsListConfiguratorFilterOptionDefinition::class,
                        [new VueComponent('moorl-properties')]
                    ],
                    [
                        ProductStreamDefinition::class,
                        PartsListConfiguratorFilterProductStreamDefinition::class,
                        [new EditField()]
                    ]
                ],
            ),
        ));
    }
}
