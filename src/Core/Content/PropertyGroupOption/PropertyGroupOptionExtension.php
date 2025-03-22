<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PropertyGroupOption;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorLogicalOptionDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorGlobalOptionDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorSecondOptionDefinition;
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
                'partsListConfigurators',
                PartsListConfiguratorDefinition::class,
                PartsListConfiguratorGlobalOptionDefinition::class,
                'property_group_option_id',
                'moorl_pl_id'
            ))->addFlags()
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'partsListConfiguratorPosts',
                PartsListConfiguratorDefinition::class,
                PartsListConfiguratorSecondOptionDefinition::class,
                'property_group_option_id',
                'moorl_pl_id'
            ))->addFlags()
        );

        $collection->add(
            (new ManyToManyAssociationField(
                'partsListConfiguratorLogicals',
                PartsListConfiguratorDefinition::class,
                PartsListConfiguratorLogicalOptionDefinition::class,
                'property_group_option_id',
                'moorl_pl_id'
            ))->addFlags()
        );
    }
}
