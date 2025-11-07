<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator;

use Shopware\Core\Content\ProductStream\ProductStreamCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListConfiguratorFilterEntity extends Entity
{
    use EntityIdTrait;

    protected string $partsListConfiguratorId = "";
    protected bool $logical = false;
    protected bool $fixed = false;
    protected int $position = 0;
    protected ?string $technicalName = null;
    protected ?PartsListConfiguratorEntity $partsListConfigurator = null;
    protected ?PropertyGroupOptionCollection $propertyGroupOptions = null;
    protected ?ProductStreamCollection $productStreams = null;
    protected ?array $logicalConfigurator = null;

    public function getPropertyGroupOptions(): ?PropertyGroupOptionCollection
    {
        return $this->propertyGroupOptions;
    }

    public function setPropertyGroupOptions(?PropertyGroupOptionCollection $propertyGroupOptions): void
    {
        $this->propertyGroupOptions = $propertyGroupOptions;
    }

    public function getProductStreams(): ?ProductStreamCollection
    {
        return $this->productStreams;
    }

    public function setProductStreams(?ProductStreamCollection $productStreams): void
    {
        $this->productStreams = $productStreams;
    }

    public function getLogicalConfigurator(): ?array
    {
        return $this->logicalConfigurator;
    }

    public function setLogicalConfigurator(?array $logicalConfigurator): void
    {
        $this->logicalConfigurator = $logicalConfigurator;
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
}
