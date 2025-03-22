<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\Seo;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use Shopware\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class PartsListConfiguratorSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'frontend.moorl.parts.list.configurator.detail';
    final public const DEFAULT_TEMPLATE = 'configurator/{{ partsListConfigurator.translated.name }}';

    public function __construct(private readonly PartsListConfiguratorDefinition $entityDefinition)
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
        if (!$entity instanceof PartsListConfiguratorEntity) {
            throw new \InvalidArgumentException('Expected PartsListConfiguratorEntity');
        }

        return new SeoUrlMapping(
            $entity,
            ['partsListConfiguratorId' => $entity->getId()],
            ['partsListConfigurator' => $entity->jsonSerialize()]
        );
    }

    public function prepareCriteria(Criteria $criteria, SalesChannelEntity $salesChannel/*, SalesChannelEntity $salesChannel */): void
    {
    }
}
