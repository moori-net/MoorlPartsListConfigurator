import MoorlPartsListConfiguratorPlugin from './parts-list-configurator/parts-list-configurator.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('MoorlPartsListConfigurator', MoorlPartsListConfiguratorPlugin, '[data-moorl-parts-list-configurator]');

if (module.hot) {
    module.hot.accept();
}
