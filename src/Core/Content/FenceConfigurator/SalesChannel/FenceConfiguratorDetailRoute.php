<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductConfiguratorLoader;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class FenceConfiguratorDetailRoute
{
    private readonly SalesChannelRepository $lookRepository;

    public function __construct(
        SalesChannelRepository $lookRepository,
        private readonly ProductConfiguratorLoader $configuratorLoader
    ) {
        $this->lookRepository = $lookRepository;
    }

    public function getDecorated(): never
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $fenceConfiguratorId, Request $request, SalesChannelContext $context, Criteria $criteria): FenceConfiguratorDetailRouteResponse
    {
        $criteria->setIds([$fenceConfiguratorId]);

        /** @var SalesChannelFenceConfiguratorEntity $look */
        $look = $this->lookRepository
            ->search($criteria, $context)
            ->first();

        $look->setProducts($look->getFenceConfiguratorProducts()->getProducts());

        /** @var SalesChannelProductEntity $product */
        foreach ($look->getProducts() as $product) {
            $product->setSortedProperties($this->configuratorLoader->load($product, $context));
        }

        return new FenceConfiguratorDetailRouteResponse($look);
    }
}
