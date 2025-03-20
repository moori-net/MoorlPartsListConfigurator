Shopware.Component.override('sw-cms-list', {
    computed: {
        sortPageTypes() {
            const sortPageTypes = this.$super('sortPageTypes');

            sortPageTypes.push({
                value: 'fence_configurator_detail',
                name: this.$tc('moorl-fence-configurator.general.fenceConfigurator')
            });

            return sortPageTypes;
        },

        pageTypes() {
            const pageTypes = this.$super('pageTypes');

            pageTypes['fence_configurator_detail'] = this.$tc('moorl-fence-configurator.general.fenceConfigurator');

            return pageTypes;
        },
    }
});
