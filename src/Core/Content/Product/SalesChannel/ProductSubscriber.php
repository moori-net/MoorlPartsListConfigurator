<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\Product\SalesChannel;

use Shopware\Core\Content\Product\Events\ProductListingResolvePreviewEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingResolvePreviewEvent::class => 'onProductListingResolvePreviewEvent',
        ];
    }

    public function onProductListingResolvePreviewEvent(ProductListingResolvePreviewEvent $event): void
    {
        foreach ($event->getMapping() as $k => $v) {
            if ($k === $v) {
                continue;
            }
            $event->replace($k, $k);
        }
    }
}
