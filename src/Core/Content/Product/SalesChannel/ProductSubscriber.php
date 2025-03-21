<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\Product\SalesChannel;

use Moorl\FenceConfigurator\Storefront\Page\FenceConfigurator\FenceConfiguratorPageLoader;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.process.criteria' => 'processCriteria'
        ];
    }

    public function processCriteria(SalesChannelProcessCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();
        if (!$criteria->hasState(FenceConfiguratorPageLoader::CRITERIA_STATE)) {
            return;
        }

        $criteria->resetGroupFields();
    }
}
