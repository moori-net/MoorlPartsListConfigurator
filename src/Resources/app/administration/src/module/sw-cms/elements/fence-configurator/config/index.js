const {Component, Mixin} = Shopware;
const Criteria = Shopware.Data.Criteria;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-fence-configurator', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    computed: {
        formRepository() {
            return this.repositoryFactory.create('moorl_fc');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-fence-configurator');
            this.initElementData('moorl-fence-configurator');
        },

        onChangeFenceConfigurator(formId) {
            if (!formId) {
                this.element.config.fenceConfigurator.value = null;
                this.$set(this.element.data, 'fenceConfigurator', null);
            } else {
                const criteria = new Criteria();

                this.formRepository.get(formId, Shopware.Context.api, criteria).then((form) => {
                    this.element.config.fenceConfigurator.value = formId;
                    this.$set(this.element.data, 'fenceConfigurator', form);
                });
            }

            this.$emit('element-update', this.element);
        },
    }
});
