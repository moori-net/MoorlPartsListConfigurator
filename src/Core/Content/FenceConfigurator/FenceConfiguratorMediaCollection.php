<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Aggregate\FenceConfiguratorMedia;

use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                    add(FenceConfiguratorMediaEntity $entity)
 * @method void                    set(string $key, FenceConfiguratorMediaEntity $entity)
 * @method FenceConfiguratorMediaEntity[]    getIterator()
 * @method FenceConfiguratorMediaEntity[]    getElements()
 * @method FenceConfiguratorMediaEntity|null get(string $key)
 * @method FenceConfiguratorMediaEntity|null first()
 * @method FenceConfiguratorMediaEntity|null last()
 */
class FenceConfiguratorMediaCollection extends EntityCollection
{
    public function getMedia(): MediaCollection
    {
        return new MediaCollection(
            $this->fmap(fn(FenceConfiguratorMediaEntity $lookMedia) => $lookMedia->getMedia())
        );
    }

    public function getApiAlias(): string
    {
        return 'moorl_fc_media_collection';
    }

    protected function getExpectedClass(): string
    {
        return FenceConfiguratorMediaEntity::class;
    }
}
