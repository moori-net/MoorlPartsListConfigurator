<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\DataAbstractionLayer;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;

class FenceConfiguratorIndexerEvent extends NestedEvent
{
    public function __construct(private readonly array $ids, private readonly Context $context)
    {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
