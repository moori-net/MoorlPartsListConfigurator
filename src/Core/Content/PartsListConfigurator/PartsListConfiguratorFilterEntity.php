<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListConfiguratorFilterEntity extends Entity
{
    use EntityIdTrait;

    protected string $partsListConfiguratorId;
    protected bool $logical = false;
    protected bool $fixed = false;
    protected array $partsListConfiguratorProductStreamIds;
    protected int $position;
    protected ?string $technicalName = null;
    protected ?PartsListConfiguratorEntity $partsListConfigurator = null;
    protected ?PropertyGroupOptionCollection $options = null;
    protected array $productStreamIds = [];
    protected ?array $logicalConfigurator = null;

    public function getLogicalConfigurator(): ?array
    {
        return $this->logicalConfigurator;
    }

    public function setLogicalConfigurator(?array $logicalConfigurator): void
    {
        $this->logicalConfigurator = $logicalConfigurator;
    }

    public function getProductStreamIds(): array
    {
        return $this->productStreamIds;
    }

    public function addProductStreamId(string $productStreamId): void
    {
        $this->productStreamIds[] = $productStreamId;
    }

    public function getLogical(): bool
    {
        return $this->logical;
    }

    public function setLogical(bool $logical): void
    {
        $this->logical = $logical;
    }

    public function getFixed(): bool
    {
        return $this->fixed;
    }

    public function setFixed(bool $fixed): void
    {
        $this->fixed = $fixed;
    }

    public function getPartsListConfiguratorId(): string
    {
        return $this->partsListConfiguratorId;
    }

    public function setPartsListConfiguratorId(string $partsListConfiguratorId): void
    {
        $this->partsListConfiguratorId = $partsListConfiguratorId;
    }

    public function getPartsListConfiguratorProductStreamIds(): array
    {
        return $this->partsListConfiguratorProductStreamIds;
    }

    public function setPartsListConfiguratorProductStreamIds(array $partsListConfiguratorProductStreamIds): void
    {
        $this->partsListConfiguratorProductStreamIds = $partsListConfiguratorProductStreamIds;
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

    public function getOptions(): ?PropertyGroupOptionCollection
    {
        return $this->options;
    }

    public function setOptions(?PropertyGroupOptionCollection $options): void
    {
        $this->options = $options;
    }
}
