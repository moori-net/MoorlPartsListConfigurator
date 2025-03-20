<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class FenceConfiguratorDetailRoute
{
    public function __construct(
        private readonly SalesChannelRepository $fenceConfiguratorRepository
    )
    {
    }

    public function getDecorated(): never
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $fenceConfiguratorId, Request $request, SalesChannelContext $context, Criteria $criteria): FenceConfiguratorDetailRouteResponse
    {
        $criteria->setIds([$fenceConfiguratorId]);

        $look = $this->fenceConfiguratorRepository
            ->search($criteria, $context)
            ->first();

        return new FenceConfiguratorDetailRouteResponse($look);
    }
}
