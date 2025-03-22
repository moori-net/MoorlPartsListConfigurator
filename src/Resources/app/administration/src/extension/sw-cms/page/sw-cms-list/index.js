Shopware.Component.override('sw-cms-list', {
    computed: {
        sortPageTypes() {
            const sortPageTypes = this.$super('sortPageTypes');

            sortPageTypes.push({
                value: 'parts_list_configurator_detail',
                name: this.$tc('moorl-parts-list-configurator.general.partsListConfigurator')
            });

            return sortPageTypes;
        },

        pageTypes() {
            const pageTypes = this.$super('pageTypes');

            pageTypes['parts_list_configurator_detail'] = this.$tc('moorl-parts-list-configurator.general.partsListConfigurator');

            return pageTypes;
        },
    }
});
