<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator\Data;

use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\PartsListConfiguratorDefinition;
use Moorl\PartsListConfigurator\Core\Content\PartsListConfigurator\Seo\PartsListConfiguratorSeoUrlRoute;
use Moorl\PartsListConfigurator\MoorlPartsListConfigurator;
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
        return MoorlPartsListConfigurator::SHOPWARE_TABLES;
    }

    public function getPluginTables(): ?array
    {
        return MoorlPartsListConfigurator::PLUGIN_TABLES;
    }

    public function getPluginName(): string
    {
        return MoorlPartsListConfigurator::NAME;
    }

    public function getCreatedAt(): string
    {
        return MoorlPartsListConfigurator::DATA_CREATED_AT;
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
            '{CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID}' => MoorlPartsListConfigurator::CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID,
            '{SEO_ROUTE_NAME}' => PartsListConfiguratorSeoUrlRoute::ROUTE_NAME,
            '{SEO_DEFAULT_TEMPLATE}' => PartsListConfiguratorSeoUrlRoute::DEFAULT_TEMPLATE,
            '{MAIN_ENTITY}' => PartsListConfiguratorDefinition::ENTITY_NAME,
        ];
    }

    public function getPreInstallQueries(): array
    {
        return [
            "UPDATE `cms_page` SET `locked` = '0' WHERE `id` = UNHEX('{CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID}');"
        ];
    }

    public function getInstallQueries(): array
    {
        return [
            "UPDATE `cms_page` SET `locked` = '1' WHERE `id` = UNHEX('{CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID}');",
            "INSERT IGNORE INTO `seo_url_template` (`id`,`is_valid`,`route_name`,`entity_name`,`template`,`created_at`) VALUES (UNHEX('{ID:SEO_URL_1}'),1,'{SEO_ROUTE_NAME}','{MAIN_ENTITY}','{SEO_DEFAULT_TEMPLATE}','{DATA_CREATED_AT}');"
        ];
    }
}
