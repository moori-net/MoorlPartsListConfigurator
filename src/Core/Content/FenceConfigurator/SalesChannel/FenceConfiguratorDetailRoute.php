<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
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
        private readonly SalesChannelRepository $fenceConfiguratorRepository,
        private readonly SalesChannelCmsPageLoaderInterface $cmsPageLoader,
        private readonly FenceConfiguratorDefinition $fenceConfiguratorDefinition
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

        $criteria->addAssociation('options.media');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('postOptions.media');
        $criteria->addAssociation('postOptions.group');
        $criteria->addAssociation('logicalOptions.media');
        $criteria->addAssociation('logicalOptions.group');

        /** @var SalesChannelFenceConfiguratorEntity $fenceConfigurator */
        $fenceConfigurator = $this->fenceConfiguratorRepository
            ->search($criteria, $context)
            ->first();

        $pageId = $fenceConfigurator->getCmsPageId();

        $slotConfig = $fenceConfigurator->getTranslation('slotConfig');
        $resolverContext = new EntityResolverContext($context, $request, $this->fenceConfiguratorDefinition, $fenceConfigurator);

        $pages = $this->cmsPageLoader->load(
            $request,
            $this->createCriteria($pageId, $request),
            $context,
            $slotConfig,
            $resolverContext
        );

        if (!$pages->has($pageId)) {
            throw new PageNotFoundException($pageId);
        }

        $fenceConfigurator->setCmsPage($pages->get($pageId));

        return new FenceConfiguratorDetailRouteResponse($fenceConfigurator);
    }

    private function createCriteria(string $pageId, Request $request): Criteria
    {
        $criteria = new Criteria([$pageId]);
        $criteria->setTitle('fence_configurator_detail::cms-page');

        $slots = $request->get('slots');

        if (\is_string($slots)) {
            $slots = explode('|', $slots);
        }

        if (!empty($slots) && \is_array($slots)) {
            $criteria
                ->getAssociation('sections.blocks')
                ->addFilter(new EqualsAnyFilter('slots.id', $slots));
        }

        return $criteria;
    }
}
