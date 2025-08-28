<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldMappingCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

class PartsListConfiguratorFilterOptionDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_filter_option';
    final public const PROPERTY_NAME = 'option';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(FieldMappingCollection::getFieldItems(
            mappingClasses: [
                PartsListConfiguratorFilterDefinition::class,
                PropertyGroupOptionDefinition::class
            ]
        ));
    }
}
