<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Aggregate\FenceConfiguratorProduct;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void            add(FenceConfiguratorProductEntity $entity)
 * @method void            set(string $key, FenceConfiguratorProductEntity $entity)
 * @method FenceConfiguratorProductEntity[]    getIterator()
 * @method FenceConfiguratorProductEntity[]    getElements()
 * @method FenceConfiguratorProductEntity|null get(string $key)
 * @method FenceConfiguratorProductEntity|null first()
 * @method FenceConfiguratorProductEntity|null last()
 */
class FenceConfiguratorProductCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FenceConfiguratorProductEntity::class;
    }

    public function getProducts(): ProductCollection
    {
        $this->sortByPriority();

        return new ProductCollection(
            $this->fmap(fn(FenceConfiguratorProductEntity $look) => $look->getProduct())
        );
    }

    public function sortByPriority(): void
    {
        $this->sort(fn(FenceConfiguratorProductEntity $a, FenceConfiguratorProductEntity $b) => $b->getPriority() <=> $a->getPriority());
    }
}
