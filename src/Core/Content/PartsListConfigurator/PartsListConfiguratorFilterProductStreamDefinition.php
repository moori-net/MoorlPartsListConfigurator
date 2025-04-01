<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\System\EntityMigrationInterface;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class PartsListConfiguratorFilterProductStreamDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_filter_product_stream';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getMigrationFields(): FieldCollection
    {
        return $this->defineFields();
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('moorl_pl_filter_id', 'partsListConfiguratorFilterId', PartsListConfiguratorFilterDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('partsListConfiguratorFilter', 'moorl_pl_filter_id', PartsListConfiguratorFilterDefinition::class, 'id'),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class, 'id'),
        ]);
    }
}
