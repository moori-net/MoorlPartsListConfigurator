import template from './sw-cms-create-wizard.html.twig';

Shopware.Component.override('sw-cms-create-wizard', {
    template,

    created() {
        this.pageTypeNames['fence_configurator_detail'] = this.$tc('moorl-fence-configurator.general.fenceConfigurator');
        this.pageTypeIcons['fence_configurator_detail'] = 'regular-3d';
    },
});
