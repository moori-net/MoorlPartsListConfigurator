<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use MoorlFoundation\Core\Content\ProductBuyList\ProductBuyListItemEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListConfiguratorFilterEntity extends Entity
{
    use EntityIdTrait;

    protected string $partsListConfiguratorId;
    protected string $productStreamId;
    protected int $position;
    protected ?string $technicalName;
    protected ?PartsListConfiguratorEntity $partsListConfigurator;
    protected ?array $productStream;

    public function getPartsListConfiguratorId(): string
    {
        return $this->partsListConfiguratorId;
    }

    public function setPartsListConfiguratorId(string $partsListConfiguratorId): void
    {
        $this->partsListConfiguratorId = $partsListConfiguratorId;
    }

    public function getProductStreamId(): string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTechnicalName(): ?string
    {
        return $this->technicalName;
    }

    public function setTechnicalName(?string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    public function getPartsListConfigurator(): ?PartsListConfiguratorEntity
    {
        return $this->partsListConfigurator;
    }

    public function setPartsListConfigurator(?PartsListConfiguratorEntity $partsListConfigurator): void
    {
        $this->partsListConfigurator = $partsListConfigurator;
    }

    public function getProductStream(): ?ProductStreamEntity
    {
        return $this->productStream;
    }

    public function setProductStream(?ProductStreamEntity $productStream): void
    {
        $this->productStream = $productStream;
    }
}
