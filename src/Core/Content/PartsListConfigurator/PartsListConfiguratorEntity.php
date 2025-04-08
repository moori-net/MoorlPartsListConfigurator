<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingBaseTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingMetaTrait;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityThingPageTrait;
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

    protected bool $active = false;
    protected ?string $coverId = null;
    protected ?string $teaser = null;
    protected string $calculator;

    protected ?PartsListConfiguratorFilterCollection $filters = null;
    protected ?PartsListConfiguratorMediaCollection $media = null;
    protected ?PartsListConfiguratorMediaEntity $cover = null;

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
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

    public function getFilters(): ?PartsListConfiguratorFilterCollection
    {
        return $this->filters;
    }

    public function setFilters(?PartsListConfiguratorFilterCollection $filters): void
    {
        $this->filters = $filters;
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
}
