<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Seo;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\DataAbstractionLayer\FenceConfiguratorIndexerEvent;
use Shopware\Core\Content\Seo\SeoUrlUpdater;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SeoUrlUpdateListener implements EventSubscriberInterface
{
    public function __construct(private readonly SeoUrlUpdater $seoUrlUpdater)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FenceConfiguratorIndexerEvent::class => 'onFenceConfiguratorIndexerEvent',
        ];
    }

    public function onFenceConfiguratorIndexerEvent(FenceConfiguratorIndexerEvent $event): void
    {
        $this->seoUrlUpdater->update(FenceConfiguratorSeoUrlRoute::ROUTE_NAME, $event->getIds());
    }
}
