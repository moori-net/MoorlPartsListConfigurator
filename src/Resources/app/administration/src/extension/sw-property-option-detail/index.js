import template from './sw-property-option-detail.html.twig';

Shopware.Component.override('sw-property-option-detail', {
    template,

    inject: [
        'customFieldDataProviderService',
    ],

    data() {
        return {
            customFieldSets: null
        };
    },

    created() {
        this.loadCustomFieldSets();

    },

    methods: {
        loadCustomFieldSets() {
            this.customFieldDataProviderService.getCustomFieldSets('property_group_option').then((sets) => {
                this.customFieldSets = sets;
            });
        },
    }
});
