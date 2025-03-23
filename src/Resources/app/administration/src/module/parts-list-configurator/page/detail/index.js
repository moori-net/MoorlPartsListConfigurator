import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria, ChangesetGenerator} = Shopware.Data;
const utils = Shopware.Utils;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();
const type = Shopware.Utils.types;
const {cloneDeep, merge} = Shopware.Utils.object;

Component.register('moorl-parts-list-configurator-detail', {
    template,

    inject: [
        'repositoryFactory',
        'cmsService',
        'seoUrlService',
        'customFieldDataProviderService',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            pageTypes: ['parts_list_configurator_detail'],
            processSuccess: false,
            customFieldSets: null
        };
    },

    computed: {
        ...mapPropertyErrors('item', ['name', 'calculator']),

        repository() {
            return this.repositoryFactory.create('moorl_pl');
        },

        defaultCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('media')
            criteria.getAssociation('seoUrls').addFilter(Criteria.equals('isCanonical', true));
            return criteria;
        },

        pageTypeCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addFilter(
                Criteria.equals('type', 'parts_list_configurator_detail'),
            );

            return criteria;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        itemMediaRepository() {
            return this.repositoryFactory.create('moorl_pl_media');
        },

        cmsPageRepository() {
            return this.repositoryFactory.create('cms_page');
        },

        cmsPageId() {
            return this.item ? this.item.cmsPageId : null;
        },

        cmsPage() {
            return Shopware.State.get('cmsPageState').currentPage;
        },

        identifier() {
            return this.placeholder(this.item, 'name');
        },

        partsListFilterFilterColumns() {
            return [
                'fixed',
                'logical',
                'position',
                'technicalName',
            ];
        },

        partsListFilterCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('options');
            return criteria;
        },

        partsListProductStreamFilterColumns() {
            return [
                'position',
                'technicalName',
                'productStream.name',
            ];
        },

        partsListProductStreamCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('productStream');
            return criteria;
        }
    },

    watch: {
        cmsPageId() {
            Shopware.State.dispatch('cmsPageState/resetCmsPageState');
            this.getAssignedCmsPage();
        }
    },

    created() {
        Shopware.State.dispatch('cmsPageState/resetCmsPageState');

        this.loadCustomFieldSets();
        this.getItem();
    },

    methods: {
        getItem() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.defaultCriteria)
                .then((entity) => {
                    this.item = entity;
                    this.getAssignedCmsPage();
                });
        },

        loadCustomFieldSets() {
            this.customFieldDataProviderService.getCustomFieldSets('moorl_pl').then((sets) => {
                this.customFieldSets = sets;
            });
        },

        onChangeLanguage() {
            this.getItem();
        },

        async onClickSave() {
            this.isLoading = true;

            await this.updateSeoUrls();

            const pageOverrides = this.getCmsPageOverrides();

            if (type.isPlainObject(pageOverrides)) {
                this.item.slotConfig = cloneDeep(pageOverrides);
            }

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    if (exception.response.data && exception.response.data.errors) {
                        exception.response.data.errors.forEach((error) => {
                            this.createNotificationWarning({
                                title: this.$tc('moorl-foundation.notification.errorTitle'),
                                message: error.detail
                            });
                        });
                    }
                });
        },

        updateSeoUrls() {
            if (!Shopware.State.list().includes('swSeoUrl')) {
                return Promise.resolve();
            }

            const seoUrls = Shopware.State.getters['swSeoUrl/getNewOrModifiedUrls']();

            return Promise.all(seoUrls.map((seoUrl) => {
                if (seoUrl.seoPathInfo) {
                    seoUrl.isModified = true;
                    return this.seoUrlService.updateCanonicalUrl(seoUrl, seoUrl.languageId);
                }

                return Promise.resolve();
            }));
        },

        saveFinish() {
            this.processSuccess = false;
        },

        getAssignedCmsPage() {
            if (this.cmsPageId === null) {
                return Promise.resolve(null);
            }

            const cmsPageId = this.cmsPageId;
            const criteria = new Criteria(1, 1);
            criteria.setIds([cmsPageId]);
            criteria.addAssociation('previewMedia');
            criteria.addAssociation('sections');
            criteria.getAssociation('sections').addSorting(Criteria.sort('position'));

            criteria.addAssociation('sections.blocks');
            criteria.getAssociation('sections.blocks')
                .addSorting(Criteria.sort('position', 'ASC'))
                .addAssociation('slots');

            return this.cmsPageRepository.search(criteria).then((response) => {
                const cmsPage = response.get(cmsPageId);

                if (cmsPageId !== this.cmsPageId) {
                    return null;
                }

                if (this.item.slotConfig !== null) {
                    cmsPage.sections.forEach((section) => {
                        section.blocks.forEach((block) => {
                            block.slots.forEach((slot) => {
                                if (this.item.slotConfig[slot.id]) {
                                    if (slot.config === null) {
                                        slot.config = {};
                                    }
                                    merge(slot.config, cloneDeep(this.item.slotConfig[slot.id]));
                                }
                            });
                        });
                    });
                }

                this.updateCmsPageDataMapping();
                Shopware.State.commit('cmsPageState/setCurrentPage', cmsPage);

                return this.cmsPage;
            });
        },

        updateCmsPageDataMapping() {
            Shopware.State.commit('cmsPageState/setCurrentMappingEntity', 'moorl_pl');
            Shopware.State.commit(
                'cmsPageState/setCurrentMappingTypes',
                this.cmsService.getEntityMappingTypes('moorl_pl'),
            );
            Shopware.State.commit('cmsPageState/setCurrentDemoEntity', this.item);
        },

        getCmsPageOverrides() {
            if (this.cmsPage === null) {
                return null;
            }

            this.deleteSpecifcKeys(this.cmsPage.sections);

            const changesetGenerator = new ChangesetGenerator();
            const {changes} = changesetGenerator.generate(this.cmsPage);

            const slotOverrides = {};
            if (changes === null) {
                return slotOverrides;
            }

            if (type.isArray(changes.sections)) {
                changes.sections.forEach((section) => {
                    if (type.isArray(section.blocks)) {
                        section.blocks.forEach((block) => {
                            if (type.isArray(block.slots)) {
                                block.slots.forEach((slot) => {
                                    slotOverrides[slot.id] = slot.config;
                                });
                            }
                        });
                    }
                });
            }

            return slotOverrides;
        },

        deleteSpecifcKeys(sections) {
            if (!sections) {
                return;
            }

            sections.forEach((section) => {
                if (!section.blocks) {
                    return;
                }

                section.blocks.forEach((block) => {
                    if (!block.slots) {
                        return;
                    }

                    block.slots.forEach((slot) => {
                        if (!slot.config) {
                            return;
                        }

                        Object.values(slot.config).forEach((configField) => {
                            if (configField.entity) {
                                delete configField.entity;
                            }
                            if (configField.hasOwnProperty('required')) {
                                delete configField.required;
                            }
                            if (configField.type) {
                                delete configField.type;
                            }
                        });
                    });
                });
            });
        },
    }
});
