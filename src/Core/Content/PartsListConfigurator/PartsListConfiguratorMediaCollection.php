<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                    add(PartsListConfiguratorMediaEntity $entity)
 * @method void                    set(string $key, PartsListConfiguratorMediaEntity $entity)
 * @method PartsListConfiguratorMediaEntity[]    getIterator()
 * @method PartsListConfiguratorMediaEntity[]    getElements()
 * @method PartsListConfiguratorMediaEntity|null get(string $key)
 * @method PartsListConfiguratorMediaEntity|null first()
 * @method PartsListConfiguratorMediaEntity|null last()
 */
class PartsListConfiguratorMediaCollection extends EntityCollection
{
    public function getMedia(): MediaCollection
    {
        return new MediaCollection(
            $this->fmap(fn(PartsListConfiguratorMediaEntity $partsListConfiguratorMedia) => $partsListConfiguratorMedia->getMedia())
        );
    }

    public function getApiAlias(): string
    {
        return 'moorl_pl_media_collection';
    }

    protected function getExpectedClass(): string
    {
        return PartsListConfiguratorMediaEntity::class;
    }
}
