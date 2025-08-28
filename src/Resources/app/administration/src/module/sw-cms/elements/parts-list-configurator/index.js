import './config';
import './component';

Shopware.Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlPartsListConfigurator',
    icon: 'regular-3d',
    name: 'moorl-parts-list-configurator',
    label: 'sw-cms.elements.moorl-parts-list-configurator.name',
    component: 'sw-cms-el-moorl-parts-list-configurator',
    configComponent: 'sw-cms-el-config-moorl-parts-list-configurator',
    previewComponent: true,
    defaultConfig: {
        partsListConfigurator: {
            source: 'static',
            value: null,
            entity: {
                name: 'moorl_pl'
            }
        }
    }
});
