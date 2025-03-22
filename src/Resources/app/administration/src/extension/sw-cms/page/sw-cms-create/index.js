Shopware.Component.override('sw-cms-create', {
    methods: {
        _onWizardComplete() {
            if (this.page.type === 'parts_list_configurator_detail') {
                this.onPageTypeChange();
            }

            this.$super('onWizardComplete')
        }
    }
});
