<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Aggregate\FenceConfiguratorProduct;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FenceConfiguratorProductDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_fc_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FenceConfiguratorProductEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FenceConfiguratorProductCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'priority' => 0,
            'productVersionId' => Defaults::LIVE_VERSION,
            'lookVersionId' => Defaults::LIVE_VERSION
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),

            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new FkField('moorl_fc_id', 'fenceConfiguratorId', FenceConfiguratorDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(FenceConfiguratorDefinition::class))->addFlags(new Required()),

            (new IntField('priority', 'priority'))->addFlags(new Required(), new EditField('number')),

            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id', true))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('look', 'moorl_fc_id', FenceConfiguratorDefinition::class, 'id', true))->addFlags(new EditField(), new LabelProperty('name'))
        ]);
    }
}
