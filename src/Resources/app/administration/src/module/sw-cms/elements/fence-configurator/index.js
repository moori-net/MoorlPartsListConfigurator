const {Application} = Shopware;

import './config';
import './component';

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFenceConfigurator',
    icon: 'regular-3d',
    name: 'moorl-fence-configurator',
    label: 'sw-cms.elements.moorl-fence-configurator.name',
    component: 'sw-cms-el-moorl-fence-configurator',
    configComponent: 'sw-cms-el-config-moorl-fence-configurator',
    previewComponent: true,
    defaultConfig: {
        fenceConfigurator: {
            source: 'static',
            value: null,
            entity: {
                name: 'moorl_fc'
            }
        }
    }
});
