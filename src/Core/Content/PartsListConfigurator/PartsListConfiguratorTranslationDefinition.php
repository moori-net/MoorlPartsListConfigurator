<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection\FieldThingCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PartsListConfiguratorTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'moorl_pl_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return PartsListConfiguratorTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return PartsListConfiguratorTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return PartsListConfiguratorDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        $collection = FieldThingCollection::getTranslatedFieldItems();

        return new FieldCollection($collection);
    }
}
