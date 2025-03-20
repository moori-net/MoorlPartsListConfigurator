<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(FenceConfiguratorTranslationEntity $entity)
 * @method void                           set(string $key, FenceConfiguratorTranslationEntity $entity)
 * @method FenceConfiguratorTranslationEntity[]    getIterator()
 * @method FenceConfiguratorTranslationEntity[]    getElements()
 * @method FenceConfiguratorTranslationEntity|null get(string $key)
 * @method FenceConfiguratorTranslationEntity|null first()
 * @method FenceConfiguratorTranslationEntity|null last()
 */
class FenceConfiguratorTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_fc_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return FenceConfiguratorTranslationEntity::class;
    }
}
