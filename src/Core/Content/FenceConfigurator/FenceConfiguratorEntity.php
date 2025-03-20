<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingBaseTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingMetaTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingPageTrait;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FenceConfiguratorEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;
    use EntityThingMetaTrait;
    use EntityThingPageTrait;
    use EntityThingBaseTrait;

    protected ?PropertyGroupOptionCollection $options = null;
    protected ?PropertyGroupOptionCollection $postOptions = null;
    protected string $productLinePropertyId;

    public function getProductLinePropertyId(): string
    {
        return $this->productLinePropertyId;
    }

    public function setProductLinePropertyId(string $productLinePropertyId): void
    {
        $this->productLinePropertyId = $productLinePropertyId;
    }

    public function getOptions(): ?PropertyGroupOptionCollection
    {
        return $this->options;
    }

    public function setOptions(?PropertyGroupOptionCollection $options): void
    {
        $this->options = $options;
    }

    public function getPostOptions(): ?PropertyGroupOptionCollection
    {
        return $this->postOptions;
    }

    public function setPostOptions(?PropertyGroupOptionCollection $postOptions): void
    {
        $this->postOptions = $postOptions;
    }
}
