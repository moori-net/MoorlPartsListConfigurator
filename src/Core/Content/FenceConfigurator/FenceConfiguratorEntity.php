<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingBaseTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingMetaTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingPageTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingTrait;
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
    protected ?PropertyGroupOptionCollection $logicalOptions = null;
    protected string $productLinePropertyId;
    protected string $fenceStreamId;
    protected string $fencePostStreamId;
    protected string $fenceOtherStreamId;
    protected bool $active = false;

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLogicalOptions(): ?PropertyGroupOptionCollection
    {
        return $this->logicalOptions;
    }

    public function setLogicalOptions(?PropertyGroupOptionCollection $logicalOptions): void
    {
        $this->logicalOptions = $logicalOptions;
    }

    public function getFenceStreamId(): string
    {
        return $this->fenceStreamId;
    }

    public function setFenceStreamId(string $fenceStreamId): void
    {
        $this->fenceStreamId = $fenceStreamId;
    }

    public function getFencePostStreamId(): string
    {
        return $this->fencePostStreamId;
    }

    public function setFencePostStreamId(string $fencePostStreamId): void
    {
        $this->fencePostStreamId = $fencePostStreamId;
    }

    public function getFenceOtherStreamId(): string
    {
        return $this->fenceOtherStreamId;
    }

    public function setFenceOtherStreamId(string $fenceOtherStreamId): void
    {
        $this->fenceOtherStreamId = $fenceOtherStreamId;
    }

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
