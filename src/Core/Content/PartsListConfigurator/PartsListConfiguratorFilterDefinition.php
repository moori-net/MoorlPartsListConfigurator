<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\VueComponent;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorFilterDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_filter';
    final public const FIXED = '_fixed';
    final public const LOGICAL = '_logical';

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
            'technicalName' => '_fixed',
            'fixed' => false,
            'logical' => false,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('moorl_pl_id', 'partsListConfiguratorId', PartsListConfiguratorDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new BoolField('fixed', 'fixed'))->addFlags(new EditField('switch')),
            (new BoolField('logical', 'logical'))->addFlags(new EditField('switch')),

            (new ListField('moorl_pl_product_stream_ids', 'partsListConfiguratorProductStreamIds', StringField::class))
                ->addFlags(new ApiAware(), new Required(), new EditField(EditField::MULTI_ID, ['entity' => 'moorl_pl_product_stream']), new LabelProperty('technicalName')),
            (new IntField('position', 'position'))->addFlags(new ApiAware(), new EditField('number')),
            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new Required(), new EditField('text')),
            (new ManyToOneAssociationField('partsListConfigurator', 'moorl_pl_id', PartsListConfiguratorDefinition::class, 'id', false))->addFlags(),
            (new ManyToManyAssociationField(
                'options',
                PropertyGroupOptionDefinition::class,
                PartsListConfiguratorFilterOptionDefinition::class,
                'moorl_pl_filter_id', 'property_group_option_id')
            )->addFlags(new ApiAware(), new CascadeDelete(), new VueComponent('moorl-properties')),
        ]);
    }
}
