<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator\Data;

use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\FenceConfiguratorDefinition;
use Moorl\FenceConfigurator\Core\Content\FenceConfigurator\Seo\FenceConfiguratorSeoUrlRoute;
use Moorl\FenceConfigurator\MoorlFenceConfigurator;
use MoorlFoundation\Core\System\DataExtension;
use MoorlFoundation\Core\System\DataInterface;

class Data extends DataExtension implements DataInterface
{
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

    public function getPluginName(): string
    {
        return MoorlFenceConfigurator::NAME;
    }

    public function getCreatedAt(): string
    {
        return MoorlFenceConfigurator::DATA_CREATED_AT;
    }

    public function getName(): string
    {
        return 'data';
    }

    public function getType(): string
    {
        return 'data';
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    public function getLocalReplacers(): array
    {
        return [
            '{CMS_PAGE_FENCE_CONFIGURATOR_DEFAULT_ID}' => MoorlFenceConfigurator::CMS_PAGE_FENCE_CONFIGURATOR_DEFAULT_ID,
            '{SEO_ROUTE_NAME}' => FenceConfiguratorSeoUrlRoute::ROUTE_NAME,
            '{SEO_DEFAULT_TEMPLATE}' => FenceConfiguratorSeoUrlRoute::DEFAULT_TEMPLATE,
            '{MAIN_ENTITY}' => FenceConfiguratorDefinition::ENTITY_NAME,
        ];
    }

    public function getPreInstallQueries(): array
    {
        return [
            "UPDATE `cms_page` SET `locked` = '0' WHERE `id` = UNHEX('{CMS_PAGE_FENCE_CONFIGURATOR_DEFAULT_ID}');"
        ];
    }

    public function getInstallQueries(): array
    {
        return [
            "UPDATE `cms_page` SET `locked` = '1' WHERE `id` = UNHEX('{CMS_PAGE_FENCE_CONFIGURATOR_DEFAULT_ID}');",
            "INSERT IGNORE INTO `seo_url_template` (`id`,`is_valid`,`route_name`,`entity_name`,`template`,`created_at`) VALUES (UNHEX('{ID:SEO_URL_1}'),1,'{SEO_ROUTE_NAME}','{MAIN_ENTITY}','{SEO_DEFAULT_TEMPLATE}','{DATA_CREATED_AT}');"
        ];
    }
}
