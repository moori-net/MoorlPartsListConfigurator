<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(FenceConfiguratorEntity $entity)
 * @method void                           set(string $key, FenceConfiguratorEntity $entity)
 * @method FenceConfiguratorEntity[]    getIterator()
 * @method FenceConfiguratorEntity[]    getElements()
 * @method FenceConfiguratorEntity|null get(string $key)
 * @method FenceConfiguratorEntity|null first()
 * @method FenceConfiguratorEntity|null last()
 */
class FenceConfiguratorCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_fc_collection';
    }

    protected function getExpectedClass(): string
    {
        return FenceConfiguratorEntity::class;
    }
}
