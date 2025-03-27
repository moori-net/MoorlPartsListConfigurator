<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Storefront\Page\PartsListConfigurator;

use Moorl\PartsListConfigurator\Core\Calculator\PartsListCalculatorInterface;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel\SalesChannelPartsListConfiguratorEntity;
use MoorlFoundation\Core\Content\PartsList\PartsListCollection;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Storefront\Page\Page;

class PartsListConfiguratorPage extends Page
{
    protected SalesChannelPartsListConfiguratorEntity $partsListConfigurator;
    protected ?CmsPageEntity $cmsPage = null;
    protected ?ProductListingResult $products = null;
    protected ?PartsListCollection $partsList = null;
    protected ?PartsListCalculatorInterface $calculator = null;
    protected ?Cart $cart = null;

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): void
    {
        $this->cart = $cart;
    }

    public function getPartsList(): ?PartsListCollection
    {
        return $this->partsList;
    }

    public function setPartsList(?PartsListCollection $partsList): PartsListConfiguratorPage
    {
        $this->partsList = $partsList;
        return $this;
    }

    public function getCalculator(): ?PartsListCalculatorInterface
    {
        return $this->calculator;
    }

    public function setCalculator(?PartsListCalculatorInterface $calculator): void
    {
        $this->calculator = $calculator;
    }

    public function getProducts(): ?ProductListingResult
    {
        return $this->products;
    }

    public function setProducts(?ProductListingResult $products): void
    {
        $this->products = $products;
    }

    public function getPartsListConfigurator(): SalesChannelPartsListConfiguratorEntity
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
