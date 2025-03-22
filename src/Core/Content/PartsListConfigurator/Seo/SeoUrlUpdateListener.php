<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\Seo;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\DataAbstractionLayer\PartsListConfiguratorIndexerEvent;
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
            PartsListConfiguratorIndexerEvent::class => 'onPartsListConfiguratorIndexerEvent',
        ];
    }

    public function onPartsListConfiguratorIndexerEvent(PartsListConfiguratorIndexerEvent $event): void
    {
        $this->seoUrlUpdater->update(PartsListConfiguratorSeoUrlRoute::ROUTE_NAME, $event->getIds());
    }
}
