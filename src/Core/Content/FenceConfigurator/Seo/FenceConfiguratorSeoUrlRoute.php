<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Seo;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorEntity;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class FenceConfiguratorSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'frontend.moorl.gtl.look.detail';
    final public const DEFAULT_TEMPLATE = 'about/{{ look.translated.name }}';

    public function __construct(private readonly FenceConfiguratorDefinition $entityDefinition)
    {
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return new SeoUrlRouteConfig(
            $this->entityDefinition,
            self::ROUTE_NAME,
            self::DEFAULT_TEMPLATE,
            true
        );
    }

    public function getMapping(Entity $entity, ?SalesChannelEntity $salesChannel): SeoUrlMapping
    {
        if (!$entity instanceof FenceConfiguratorEntity) {
            throw new \InvalidArgumentException('Expected FenceConfiguratorEntity');
        }

        return new SeoUrlMapping(
            $entity,
            ['fenceConfiguratorId' => $entity->getId()],
            ['look' => $entity->jsonSerialize()]
        );
    }

    public function prepareCriteria(Criteria $criteria, SalesChannelEntity $salesChannel/*, SalesChannelEntity $salesChannel */): void
    {
    }
}
