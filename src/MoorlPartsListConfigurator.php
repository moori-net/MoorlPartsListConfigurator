<?php declare(strict_types=1);

namespace Moorl\PartsListConfigurator;

use MoorlFoundation\Core\PluginLifecycleHelper;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class MoorlPartsListConfigurator extends Plugin
{
    final public const CMS_PAGE_PARTS_LIST_CONFIGURATOR_DEFAULT_ID = 'e48001efe482dda2a0970ff518315ec7';
    final public const NAME = 'MoorlPartsListConfigurator';
    final public const DATA_CREATED_AT = '2025-01-03 00:00:00.000';
    final public const PLUGIN_TABLES = [
        'moorl_pl',
        'moorl_pl_translation',
        'moorl_pl_fixed_option',
        'moorl_pl_global_option',
        'moorl_pl_first_option',
        'moorl_pl_second_option',
        'moorl_pl_third_option',
        'moorl_pl_logical_option',
        'moorl_pl_product',
        'moorl_pl_media',
    ];
    final public const SHOPWARE_TABLES = [
        'cms_page',
        'cms_page_translation',
        'cms_section',
        'cms_block',
        'category',
        'category_translation',
        'property_group',
        'product_stream', /* Insert before products because indexing */
        'product',
        'product_translation',
        'product_category',
        'product_visibility',
        'product_option',
        'product_configurator_setting',
        'custom_field_set'
    ];

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        PluginLifecycleHelper::update(self::class, $this->container);
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        PluginLifecycleHelper::update(self::class, $this->container);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        if ($uninstallContext->keepUserData()) {
            return;
        }

        PluginLifecycleHelper::uninstall(self::class, $this->container);
    }
}
