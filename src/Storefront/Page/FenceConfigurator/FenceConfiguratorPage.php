<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Storefront\Page\FenceConfigurator;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Storefront\Page\Page;

class FenceConfiguratorPage extends Page
{
    protected FenceConfiguratorEntity $fenceConfigurator;
    protected ?CmsPageEntity $cmsPage = null;
    protected ?ProductListingResult $products = null;

    public function getProducts(): ?ProductListingResult
    {
        return $this->products;
    }

    public function setProducts(?ProductListingResult $products): void
    {
        $this->products = $products;
    }

    public function getFenceConfigurator(): FenceConfiguratorEntity
    {
        return $this->fenceConfigurator;
    }

    public function setFenceConfigurator(FenceConfiguratorEntity $fenceConfigurator): void
    {
        $this->fenceConfigurator = $fenceConfigurator;
    }

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

    public function getEntityName(): string
    {
        return FenceConfiguratorDefinition::ENTITY_NAME;
    }
}
