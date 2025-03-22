<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(PartsListConfiguratorTranslationEntity $entity)
 * @method void                           set(string $key, PartsListConfiguratorTranslationEntity $entity)
 * @method PartsListConfiguratorTranslationEntity[]    getIterator()
 * @method PartsListConfiguratorTranslationEntity[]    getElements()
 * @method PartsListConfiguratorTranslationEntity|null get(string $key)
 * @method PartsListConfiguratorTranslationEntity|null first()
 * @method PartsListConfiguratorTranslationEntity|null last()
 */
class PartsListConfiguratorTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_pl_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return PartsListConfiguratorTranslationEntity::class;
    }
}
