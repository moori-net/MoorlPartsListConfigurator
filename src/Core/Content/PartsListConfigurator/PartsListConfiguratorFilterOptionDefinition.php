<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\System\EntityMigrationInterface;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class PartsListConfiguratorFilterOptionDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_filter_option';

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
            (new FkField('property_group_option_id', 'optionId', PropertyGroupOptionDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('partsListConfiguratorFilter', 'moorl_pl_filter_id', PartsListConfiguratorFilterDefinition::class, 'id'),
            new ManyToOneAssociationField('option', 'property_group_option_id', PropertyGroupOptionDefinition::class, 'id'),
        ]);
    }
}
