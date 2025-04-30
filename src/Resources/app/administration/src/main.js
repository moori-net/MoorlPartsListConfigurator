function onMoorlFoundationReady(callback) {
    if (window.MoorlFoundation) {
        callback();
    } else {
        window.addEventListener('MoorlFoundationReady', callback, { once: true });
    }
}

onMoorlFoundationReady(() => {
    MoorlFoundation.ModuleHelper.registerModule({
        entity: 'moorl_pl',
        name: 'moorl-parts-list-configurator',
        navigationParent: 'sw-catalogue',
        pageType: 'parts_list_configurator_detail',
        properties: [
            {name: 'active', visibility: 100},
            {name: 'name', visibility: 200},
        ],
        pluginName: 'MoorlPartsListConfigurator',
        demoName: 'standard',
        entityMapping: {
            filters: {
                tab: 'relations',
                componentName: 'moorl-entity-grid-card-v2'
            }
        },
        cmsElements: [
            {
                name: 'parts-list-configurator-listing',
                parent: 'listing',
                icon: 'regular-view-grid',
                cmsElementEntity: {
                    associations: ['cover.media'],
                    propertyMapping: {
                        media: 'cover.media',
                        name: ['translated.name', 'name'],
                        description: ['translated.teaser', 'teaser'],
                    },
                }
            }
        ]
    });

    MoorlFoundation.ModuleHelper.registerModule({
        entity: 'moorl_pl_filter',
        name: 'moorl-parts-list-filter',
        properties: [
            {name: 'position', visibility: 200},
            {name: 'fixed', visibility: 50},
            {name: 'logical', visibility: 100},
            {name: 'technicalName', visibility: 100},
            {name: 'partsListConfigurator.name', visibility: 100},
            {name: 'options', visibility: 0},
        ],
        entityMapping: {
            options: {
                tab: 'relations',
                card: 'relations',
            }
        },
    });
});

import './extension';
import './module';
