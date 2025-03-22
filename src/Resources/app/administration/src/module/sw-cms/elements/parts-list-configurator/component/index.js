const {Component, Mixin} = Shopware;
import template from './index.html.twig';

Component.register('sw-cms-el-moorl-parts-list-configurator', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
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
