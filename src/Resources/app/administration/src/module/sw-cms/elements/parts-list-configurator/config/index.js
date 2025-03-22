const {Component, Mixin} = Shopware;
const Criteria = Shopware.Data.Criteria;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-parts-list-configurator', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    computed: {
        formRepository() {
            return this.repositoryFactory.create('moorl_pl');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-parts-list-configurator');
            this.initElementData('moorl-parts-list-configurator');
        },

        onChangePartsListConfigurator(formId) {
            if (!formId) {
                this.element.config.partsListConfigurator.value = null;
                this.$set(this.element.data, 'partsListConfigurator', null);
            } else {
                const criteria = new Criteria();

                this.formRepository.get(formId, Shopware.Context.api, criteria).then((form) => {
                    this.element.config.partsListConfigurator.value = formId;
                    this.$set(this.element.data, 'partsListConfigurator', form);
                });
            }

            this.$emit('element-update', this.element);
        },
    }
});
