<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(PartsListConfiguratorEntity $entity)
 * @method void                           set(string $key, PartsListConfiguratorEntity $entity)
 * @method PartsListConfiguratorEntity[]    getIterator()
 * @method PartsListConfiguratorEntity[]    getElements()
 * @method PartsListConfiguratorEntity|null get(string $key)
 * @method PartsListConfiguratorEntity|null first()
 * @method PartsListConfiguratorEntity|null last()
 */
class PartsListConfiguratorCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_pl_collection';
    }

    protected function getExpectedClass(): string
    {
        return PartsListConfiguratorEntity::class;
    }
}
