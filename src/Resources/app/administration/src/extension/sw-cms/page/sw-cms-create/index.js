Shopware.Component.override('sw-cms-create', {
    methods: {
        _onWizardComplete() {
            if (this.page.type === 'fence_configurator_detail') {
                this.onPageTypeChange();
            }

            this.$super('onWizardComplete')
        }
    }
});
