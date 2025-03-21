<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FenceConfiguratorMediaEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    protected string $fenceConfiguratorId;
    protected string $mediaId;
    protected int $position;
    protected ?MediaEntity $media = null;
    protected ?FenceConfiguratorEntity $fenceConfigurator = null;

    public function getFenceConfiguratorId(): string
    {
        return $this->fenceConfiguratorId;
    }

    public function setFenceConfiguratorId(string $fenceConfiguratorId): void
    {
        $this->fenceConfiguratorId = $fenceConfiguratorId;
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getFenceConfigurator(): ?FenceConfiguratorEntity
    {
        return $this->fenceConfigurator;
    }

    public function setFenceConfigurator(?FenceConfiguratorEntity $fenceConfigurator): void
    {
        $this->fenceConfigurator = $fenceConfigurator;
    }
}
