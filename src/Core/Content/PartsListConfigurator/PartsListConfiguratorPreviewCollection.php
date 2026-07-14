<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                    add(PartsListConfiguratorPreviewEntity $entity)
 * @method void                    set(string $key, PartsListConfiguratorPreviewEntity $entity)
 * @method PartsListConfiguratorPreviewEntity[]    getIterator()
 * @method PartsListConfiguratorPreviewEntity[]    getElements()
 * @method PartsListConfiguratorPreviewEntity|null get(string $key)
 * @method PartsListConfiguratorPreviewEntity|null first()
 * @method PartsListConfiguratorPreviewEntity|null last()
 */
class PartsListConfiguratorPreviewCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_pl_preview_collection';
    }

    protected function getExpectedClass(): string
    {
        return PartsListConfiguratorPreviewEntity::class;
    }
}
