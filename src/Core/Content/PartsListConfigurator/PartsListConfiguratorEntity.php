<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Moorl\PartsListConfigurator\MoorlPartsListConfigurator;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingBaseTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingMetaTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingPageTrait;
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

    protected ?PropertyGroupOptionCollection $fixedOptions = null;
    protected ?PropertyGroupOptionCollection $globalOptions = null;
    protected ?PropertyGroupOptionCollection $firstOptions = null;
    protected ?PropertyGroupOptionCollection $secondOptions = null;
    protected ?PropertyGroupOptionCollection $thirdOptions = null;
    protected ?PropertyGroupOptionCollection $logicalOptions = null;
    protected ?PartsListConfiguratorMediaCollection $media = null;
    protected ?PartsListConfiguratorMediaEntity $cover = null;
    protected ?string $coverId = null;
    protected ?string $teaser = null;
    protected string $calculator;
    protected string $firstStreamId;
    protected string $secondStreamId;
    protected string $thirdStreamId;
    protected bool $active = false;

    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId ?: MoorlPartsListConfigurator::CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID;
    }

    public function getFirstOptions(): ?PropertyGroupOptionCollection
    {
        return $this->firstOptions;
    }

    public function setFirstOptions(?PropertyGroupOptionCollection $firstOptions): void
    {
        $this->firstOptions = $firstOptions;
    }

    public function getThirdOptions(): ?PropertyGroupOptionCollection
    {
        return $this->thirdOptions;
    }

    public function setThirdOptions(?PropertyGroupOptionCollection $thirdOptions): void
    {
        $this->thirdOptions = $thirdOptions;
    }

    public function getFixedOptions(): ?PropertyGroupOptionCollection
    {
        return $this->fixedOptions;
    }

    public function setFixedOptions(?PropertyGroupOptionCollection $fixedOptions): void
    {
        $this->fixedOptions = $fixedOptions;
    }

    public function getGlobalOptions(): ?PropertyGroupOptionCollection
    {
        return $this->globalOptions;
    }

    public function setGlobalOptions(?PropertyGroupOptionCollection $globalOptions): void
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

    public function getLogicalOptions(): ?PropertyGroupOptionCollection
    {
        return $this->logicalOptions;
    }

    public function setLogicalOptions(?PropertyGroupOptionCollection $logicalOptions): void
    {
        $this->logicalOptions = $logicalOptions;
    }

    public function getMedia(): ?PartsListConfiguratorMediaCollection
    {
        return $this->media;
    }

    public function setMedia(?PartsListConfiguratorMediaCollection $media): void
    {
        $this->media = $media;
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

    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function getCalculator(): string
    {
        return $this->calculator;
    }

    public function setCalculator(string $calculator): void
    {
        $this->calculator = $calculator;
    }

    public function getFirstStreamId(): string
    {
        return $this->firstStreamId;
    }

    public function setFirstStreamId(string $firstStreamId): void
    {
        $this->firstStreamId = $firstStreamId;
    }

    public function getSecondStreamId(): string
    {
        return $this->secondStreamId;
    }

    public function setSecondStreamId(string $secondStreamId): void
    {
        $this->secondStreamId = $secondStreamId;
    }

    public function getThirdStreamId(): string
    {
        return $this->thirdStreamId;
    }

    public function setThirdStreamId(string $thirdStreamId): void
    {
        $this->thirdStreamId = $thirdStreamId;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
