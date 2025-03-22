<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Storefront\Page\Page;

class PartsListConfiguratorPage extends Page
{
    protected PartsListConfiguratorEntity $partsListConfigurator;
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

    public function getPartsListConfigurator(): PartsListConfiguratorEntity
    {
        return $this->partsListConfigurator;
    }

    public function setPartsListConfigurator(PartsListConfiguratorEntity $partsListConfigurator): void
    {
        $this->partsListConfigurator = $partsListConfigurator;
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
        return PartsListConfiguratorDefinition::ENTITY_NAME;
    }
}
