<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldThingCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FenceConfiguratorTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'moorl_fc_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return FenceConfiguratorTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return FenceConfiguratorTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return FenceConfiguratorDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        $collection = FieldThingCollection::getTranslatedFieldItems();

        return new FieldCollection($collection);
    }
}
