import MoorlFenceConfiguratorPlugin from './fence-configurator/fence-configurator.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('MoorlFenceConfigurator', MoorlFenceConfiguratorPlugin, '[data-moorl-fence-configurator]');

if (module.hot) {
    module.hot.accept();
}
