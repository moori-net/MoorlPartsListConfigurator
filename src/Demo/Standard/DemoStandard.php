<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Demo\Standard;

use Moorl\PartsListConfigurator\MoorlPartsListConfigurator;
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
        return MoorlPartsListConfigurator::SHOPWARE_TABLES;
    }

    public function getPluginTables(): ?array
    {
        return MoorlPartsListConfigurator::PLUGIN_TABLES;
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function getPluginName(): string
    {
        return MoorlPartsListConfigurator::NAME;
    }

    public function getCreatedAt(): string
    {
        return MoorlPartsListConfigurator::DATA_CREATED_AT;
    }
}
