<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class FenceConfiguratorOptionDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'moorl_fc_option';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('moorl_fc_id', 'fenceConfiguratorId', FenceConfiguratorDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('property_group_option_id', 'optionId', PropertyGroupOptionDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('fenceConfigurator', 'moorl_fc_id', FenceConfiguratorDefinition::class, 'id'),
            new ManyToOneAssociationField('option', 'property_group_option_id', PropertyGroupOptionDefinition::class, 'id'),
        ]);
    }
}
