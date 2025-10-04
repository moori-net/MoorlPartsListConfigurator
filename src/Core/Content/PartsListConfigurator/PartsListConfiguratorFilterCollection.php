<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                    add(PartsListConfiguratorFilterEntity $entity)
 * @method void                    set(string $key, PartsListConfiguratorFilterEntity $entity)
 * @method PartsListConfiguratorFilterEntity[]    getIterator()
 * @method PartsListConfiguratorFilterEntity[]    getElements()
 * @method PartsListConfiguratorFilterEntity|null get(string $key)
 * @method PartsListConfiguratorFilterEntity|null first()
 * @method PartsListConfiguratorFilterEntity|null last()
 */
class PartsListConfiguratorFilterCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_pl_filter_collection';
    }

    protected function getExpectedClass(): string
    {
        return PartsListConfiguratorFilterEntity::class;
    }
    public function getByTechnicalName(string $technicalName): ?PartsListConfiguratorFilterEntity
    {
        return $this->filter(
            fn(PartsListConfiguratorFilterEntity $entity) => $entity->getTechnicalName() === $technicalName
        )->first();
    }

    public function sortByPosition(): self
    {
        foreach ($this->getIterator() as $element) {
            if ($element->getPropertyGroupOptions() === null) {
                continue;
            }

            $element->getPropertyGroupOptions()->sort(
                fn(PropertyGroupOptionEntity $a, PropertyGroupOptionEntity $b) => $this->getPosition($a) > $this->getPosition($b)
            );

            $element->getPropertyGroupOptions()->sort(
                fn(PropertyGroupOptionEntity $a, PropertyGroupOptionEntity $b) => strnatcasecmp($a->getTranslation('name'), $b->getTranslation('name'))
            );
        }

        $this->sort(fn(PartsListConfiguratorFilterEntity $a, PartsListConfiguratorFilterEntity $b) => $a->getPosition() > $b->getPosition());

        return $this;
    }

    private function getPosition(PropertyGroupOptionEntity $a): int
    {
        return (int)($a->getTranslation('position') ?: $a->getPosition());
    }
}
