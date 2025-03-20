<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Aggregate\FenceConfiguratorProduct;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class FenceConfiguratorProductEntity extends Entity
{
    use EntityIdTrait;

    protected string $productId;
    protected string $productVersionId;
    protected string $fenceConfiguratorId;
    protected string $lookVersionId;
    protected ?ProductEntity $product = null;
    protected ?FenceConfiguratorEntity $look = null;
    protected int $priority;

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductVersionId(): string
    {
        return $this->productVersionId;
    }

    public function setProductVersionId(string $productVersionId): void
    {
        $this->productVersionId = $productVersionId;
    }

    public function getFenceConfiguratorId(): string
    {
        return $this->fenceConfiguratorId;
    }

    public function setFenceConfiguratorId(string $fenceConfiguratorId): void
    {
        $this->fenceConfiguratorId = $fenceConfiguratorId;
    }

    public function getFenceConfiguratorVersionId(): string
    {
        return $this->lookVersionId;
    }

    public function setFenceConfiguratorVersionId(string $lookVersionId): void
    {
        $this->lookVersionId = $lookVersionId;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getFenceConfigurator(): ?FenceConfiguratorEntity
    {
        return $this->look;
    }

    public function setFenceConfigurator(?FenceConfiguratorEntity $look): void
    {
        $this->look = $look;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }
}
