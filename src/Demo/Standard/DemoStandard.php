<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Demo\Standard;

use Moorl\FenceConfigurator\MoorlFenceConfigurator;
use MoorlFoundation\Core\System\DataExtension;
use MoorlFoundation\Core\System\DataInterface;

class DemoStandard extends DataExtension implements DataInterface
{
    public function getName(): string
    {
        return 'standard';
    }

    public function getType(): string
    {
        return 'demo';
    }

    public function getTables(): ?array
    {
        return array_merge(
            $this->getShopwareTables(),
            $this->getPluginTables()
        );
    }

    public function getShopwareTables(): ?array
    {
        return MoorlFenceConfigurator::SHOPWARE_TABLES;
    }

    public function getPluginTables(): ?array
    {
        return MoorlFenceConfigurator::PLUGIN_TABLES;
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function getPluginName(): string
    {
        return MoorlFenceConfigurator::NAME;
    }

    public function getCreatedAt(): string
    {
        return MoorlFenceConfigurator::DATA_CREATED_AT;
    }
}
