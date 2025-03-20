const {Module} = Shopware;

import './component/properties';
import './page/list';
import './page/detail';
import './page/create';
import './style/main.scss';

Module.register('moorl-fence-configurator', {
    type: 'plugin',
    name: 'moorl-fence-configurator',
    title: 'moorl-fence-configurator.general.fenceConfigurator',
    color: '#F88962',
    icon: 'regular-3d',
    entity: 'moorl_fc',

    routes: {
        list: {
            component: 'moorl-fence-configurator-list',
            path: 'list',
            meta: {
                privilege: 'moorl_fc:read'
            }
        },
        detail: {
            component: 'moorl-fence-configurator-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'moorl.fence.configurator.list',
                privilege: 'moorl_fc:read'
            }
        },
        create: {
            component: 'moorl-fence-configurator-create',
            path: 'create',
            meta: {
                parentPath: 'moorl.fence.configurator.list',
                privilege: 'moorl_fc:read'
            }
        }
    },

    navigation: [{
        label: 'moorl-fence-configurator.general.fenceConfigurator',
        color: '#F88962',
        icon: 'regular-3d',
        path: 'moorl.fence.configurator.list',
        position: 202,
        parent: 'sw-catalogue'
    }]
});
