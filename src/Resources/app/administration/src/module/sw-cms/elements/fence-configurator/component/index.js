const {Component, Mixin} = Shopware;
import template from './index.html.twig';

Component.register('sw-cms-el-moorl-fence-configurator', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        fenceConfigurator() {
            return this.element.data?.fenceConfigurator;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-fence-configurator');
            this.initElementData('moorl-fence-configurator');
        }
    }
});
