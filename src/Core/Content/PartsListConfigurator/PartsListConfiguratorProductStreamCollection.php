<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                    add(PartsListConfiguratorProductStreamEntity $entity)
 * @method void                    set(string $key, PartsListConfiguratorProductStreamEntity $entity)
 * @method PartsListConfiguratorProductStreamEntity[]    getIterator()
 * @method PartsListConfiguratorProductStreamEntity[]    getElements()
 * @method PartsListConfiguratorProductStreamEntity|null get(string $key)
 * @method PartsListConfiguratorProductStreamEntity|null first()
 * @method PartsListConfiguratorProductStreamEntity|null last()
 */
class PartsListConfiguratorProductStreamCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_pl_product_stream_collection';
    }

    protected function getExpectedClass(): string
    {
        return PartsListConfiguratorProductStreamEntity::class;
    }

    public function getByTechnicalName(string $technicalName): ?PartsListConfiguratorProductStreamEntity
    {
        return $this->filter(
            fn(PartsListConfiguratorProductStreamEntity $entity) => $entity->getTechnicalName() === $technicalName
        )->first();
    }
}
