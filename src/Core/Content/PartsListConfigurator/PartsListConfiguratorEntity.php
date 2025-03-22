<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingBaseTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingMetaTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingPageTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingTrait;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListConfiguratorEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;
    use EntityThingMetaTrait;
    use EntityThingPageTrait;
    use EntityThingBaseTrait;

    protected ?PropertyGroupOptionCollection $globalOptions = null;
    protected ?PropertyGroupOptionCollection $secondOptions = null;
    protected ?PropertyGroupOptionCollection $logicalOptions = null;
    protected ?PartsListConfiguratorMediaCollection $media = null;
    protected ?PartsListConfiguratorMediaEntity $cover = null;
    protected ?string $coverId = null;
    protected ?string $teaser = null;
    protected ?string $calculator = null;

    public function getCalculator(): ?string
    {
        return $this->calculator;
    }

    public function setCalculator(?string $calculator): void
    {
        $this->calculator = $calculator;
    }

    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function getCover(): ?PartsListConfiguratorMediaEntity
    {
        return $this->cover;
    }

    public function setCover(?PartsListConfiguratorMediaEntity $cover): void
    {
        $this->cover = $cover;
    }

    public function getCoverId(): ?string
    {
        return $this->coverId;
    }

    public function setCoverId(?string $coverId): void
    {
        $this->coverId = $coverId;
    }
    protected string $productLinePropertyId;
    protected string $firstStreamId;
    protected string $secondStreamId;
    protected string $thirdStreamId;
    protected bool $active = false;

    public function getMedia(): ?PartsListConfiguratorMediaCollection
    {
        return $this->media;
    }

    public function setMedia(?PartsListConfiguratorMediaCollection $media): void
    {
        $this->media = $media;
    }

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
        return $this->firstStreamId;
    }

    public function setFenceStreamId(string $firstStreamId): void
    {
        $this->firstStreamId = $firstStreamId;
    }

    public function getFencePostStreamId(): string
    {
        return $this->secondStreamId;
    }

    public function setFencePostStreamId(string $secondStreamId): void
    {
        $this->secondStreamId = $secondStreamId;
    }

    public function getFenceOtherStreamId(): string
    {
        return $this->thirdStreamId;
    }

    public function setFenceOtherStreamId(string $thirdStreamId): void
    {
        $this->thirdStreamId = $thirdStreamId;
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
        return $this->globalOptions;
    }

    public function setOptions(?PropertyGroupOptionCollection $globalOptions): void
    {
        $this->globalOptions = $globalOptions;
    }

    public function getSecondOptions(): ?PropertyGroupOptionCollection
    {
        return $this->secondOptions;
    }

    public function setSecondOptions(?PropertyGroupOptionCollection $secondOptions): void
    {
        $this->secondOptions = $secondOptions;
    }
}
