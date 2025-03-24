<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorProductStreamDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_product_stream';
    final public const FIXED = '_fixed';
    final public const LOGICAL = '_logical';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return PartsListConfiguratorProductStreamCollection::class;
    }

    public function getEntityClass(): string
    {
        return PartsListConfiguratorProductStreamEntity::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return PartsListConfiguratorDefinition::class;
    }

    public function getDefaults(): array
    {
        return [
            'position' => 0,
            'technicalName' => self::FIXED,
            'accessory' => false,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new FkField('moorl_pl_id', 'partsListConfiguratorId', PartsListConfiguratorDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new BoolField('accessory', 'accessory'))->addFlags(new ApiAware(), new EditField('switch')),
            (new IntField('position', 'position'))->addFlags(new ApiAware(), new EditField('number')),
            (new StringField('technical_name', 'technicalName'))->addFlags(new ApiAware(), new Required(), new EditField('text')),
            (new ManyToOneAssociationField('partsListConfigurator', 'moorl_pl_id', PartsListConfiguratorDefinition::class, 'id', false))->addFlags(),
            (new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class, 'id', true))->addFlags(new ApiAware(), new EditField())
        ]);
    }
}
