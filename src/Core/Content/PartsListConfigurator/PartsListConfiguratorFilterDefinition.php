<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
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
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('moorl_pl_id', 'partsListConfiguratorId', PartsListConfiguratorDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ListField('moorl_pl_product_stream_ids', 'partsListConfiguratorProductStreamIds', StringField::class))->addFlags(new ApiAware(), new Required()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('partsListConfigurator', 'moorl_pl_id', PartsListConfiguratorDefinition::class, 'id', false))->addFlags(),
            (new ManyToManyAssociationField('options', PropertyGroupOptionDefinition::class, PartsListConfiguratorFilterOptionDefinition::class, 'moorl_pl_filter_id', 'property_group_option_id'))->addFlags(new ApiAware(), new CascadeDelete()),
        ]);
    }
}
