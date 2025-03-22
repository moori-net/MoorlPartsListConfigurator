import template from './sw-cms-create-wizard.html.twig';

Shopware.Component.override('sw-cms-create-wizard', {
    template,

    created() {
        this.pageTypeNames['parts_list_configurator_detail'] = this.$tc('moorl-parts-list-configurator.general.partsListConfigurator');
        this.pageTypeIcons['parts_list_configurator_detail'] = 'regular-3d';
    },
});
