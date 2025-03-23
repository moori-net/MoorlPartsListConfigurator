<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PropertyGroupOption;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PropertyGroupOptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return PropertyGroupOptionDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
    }
}
