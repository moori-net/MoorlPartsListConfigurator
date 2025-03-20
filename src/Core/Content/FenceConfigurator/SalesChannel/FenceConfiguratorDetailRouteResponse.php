<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Core\Content\FenceConfigurator\SalesChannel;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class FenceConfiguratorDetailRouteResponse extends StoreApiResponse
{
    protected $object;

    public function __construct(FenceConfiguratorEntity $look)
    {
        parent::__construct(new ArrayStruct([
            'moorl_fc' => $look,
        ], 'moorl_fc_detail'));
    }

    public function getResult(): ArrayStruct
    {
        return $this->object;
    }

    public function getFenceConfigurator(): FenceConfiguratorEntity
    {
        return $this->object->get('moorl_fc');
    }
}
