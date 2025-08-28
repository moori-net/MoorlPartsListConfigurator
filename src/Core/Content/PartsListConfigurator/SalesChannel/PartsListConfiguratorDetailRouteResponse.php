<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\SalesChannel;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PartsListConfiguratorDetailRouteResponse extends StoreApiResponse
{
    public function __construct(PartsListConfiguratorEntity $partsListConfigurator)
    {
        parent::__construct(new ArrayStruct([
            'moorl_pl' => $partsListConfigurator,
        ], 'moorl_pl_detail'));
    }

    public function getResult(): ArrayStruct
    {
        return $this->object;
    }

    public function getPartsListConfigurator(): PartsListConfiguratorEntity
    {
        return $this->object->get('moorl_pl');
    }
}
