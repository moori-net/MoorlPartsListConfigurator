import './page/list';
import './page/detail';
import './page/create';
import './style/main.scss';

Shopware.Module.register('moorl-parts-list-configurator', {
    type: 'plugin',
    name: 'moorl-parts-list-configurator',
    title: 'moorl-parts-list-configurator.general.partsListConfigurator',
    color: '#F88962',
    icon: 'regular-3d',
    entity: 'moorl_pl',

    routes: {
        list: {
            component: 'moorl-parts-list-configurator-list',
            path: 'list',
            meta: {
                privilege: 'moorl_pl:read'
            }
        },
        detail: {
            component: 'moorl-parts-list-configurator-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'moorl.parts.list.configurator.list',
                privilege: 'moorl_pl:read'
            }
        },
        create: {
            component: 'moorl-parts-list-configurator-create',
            path: 'create',
            meta: {
                parentPath: 'moorl.parts.list.configurator.list',
                privilege: 'moorl_pl:read'
            }
        }
    },

    navigation: [{
        label: 'moorl-parts-list-configurator.general.partsListConfigurator',
        color: '#F88962',
        icon: 'regular-3d',
        path: 'moorl.parts.list.configurator.list',
        position: 202,
        parent: 'sw-catalogue'
    }]
});
