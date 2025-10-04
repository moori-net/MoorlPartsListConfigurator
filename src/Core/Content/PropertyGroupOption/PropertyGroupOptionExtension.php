<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PropertyGroupOption;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorFilterOptionDefinition;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PropertyGroupOptionExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                PartsListConfiguratorFilterDefinition::EXTENSION_COLLECTION_NAME,
                PartsListConfiguratorFilterDefinition::class,
                PartsListConfiguratorFilterOptionDefinition::class,

                'property_group_option_id',
                'moorl_pl_filter_id'
            ))
        );
    }

    public function getEntityName(): string
    {
        return PropertyGroupOptionDefinition::ENTITY_NAME;
    }
}
