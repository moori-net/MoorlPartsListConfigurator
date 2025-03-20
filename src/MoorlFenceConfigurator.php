<?php declare(strict_types=1);

namespace Moorl\FenceConfigurator;

use MoorlFoundation\Core\PluginLifecycleHelper;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class MoorlFenceConfigurator extends Plugin
{
    final public const NAME = 'MoorlFenceConfigurator';
    final public const DATA_CREATED_AT = '2025-01-03 00:00:00.000';
    final public const PLUGIN_TABLES = [
        'moorl_fc',
        'moorl_fc_translation',
        'moorl_fc_option',
        'moorl_fc_post_option',
        'moorl_fc_product',
        'moorl_fc_media',
    ];
    final public const SHOPWARE_TABLES = [
        'category',
        'category_translation',
        'property_group',
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
