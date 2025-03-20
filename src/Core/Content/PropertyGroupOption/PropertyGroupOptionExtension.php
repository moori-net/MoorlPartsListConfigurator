<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\PropertyGroupOption;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorLogicalOptionDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorOptionDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorPostOptionDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PropertyGroupOptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return PropertyGroupOptionDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'fenceConfigurators',
                FenceConfiguratorDefinition::class,
                FenceConfiguratorOptionDefinition::class,
                'property_group_option_id',
                'moorl_fc_id'
            ))->addFlags()
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'fenceConfiguratorPosts',
                FenceConfiguratorDefinition::class,
                FenceConfiguratorPostOptionDefinition::class,
                'property_group_option_id',
                'moorl_fc_id'
            ))->addFlags()
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'fenceConfiguratorLogicals',
                FenceConfiguratorDefinition::class,
                FenceConfiguratorLogicalOptionDefinition::class,
                'property_group_option_id',
                'moorl_fc_id'
            ))->addFlags()
        );
    }
}
