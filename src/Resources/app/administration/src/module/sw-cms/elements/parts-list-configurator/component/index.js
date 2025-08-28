import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-moorl-parts-list-configurator', {
    template,

    mixins: [
        Shopware.Mixin.getByName('cms-element')
    ],

    computed: {
        partsListConfigurator() {
            return this.element.data?.partsListConfigurator;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-parts-list-configurator');
            this.initElementData('moorl-parts-list-configurator');
        }
    }
});
