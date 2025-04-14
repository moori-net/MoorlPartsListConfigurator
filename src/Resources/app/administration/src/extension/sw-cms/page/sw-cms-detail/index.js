const { Criteria } = Shopware.Data;

Shopware.Component.override('sw-cms-detail', {
    computed: {
        cmsPageTypes() {
            const cmsPageTypes = this.$super('cmsPageTypes');

            cmsPageTypes['parts_list_configurator_detail'] = this.$tc('moorl-parts-list-configurator.general.partsListConfigurator');

            return cmsPageTypes;
        },

        cmsTypeMappingEntities() {
            const cmsTypeMappingEntities = this.$super('cmsTypeMappingEntities');

            cmsTypeMappingEntities['parts_list_configurator_detail'] = {
                entity: 'moorl_pl',
                mode: 'single',
            };

            return cmsTypeMappingEntities;
        },

        cmsPageTypeSettings() {
            if (this.page.type === 'parts_list_configurator_detail') {
                return {
                    entity: 'moorl_pl',
                    mode: 'single',
                };
            }

            return this.$super('cmsPageTypeSettings');
        },
    },

    methods: {
        onDemoEntityChange(demoEntityId) {
            const demoMappingType = this.cmsPageTypeSettings?.entity;

            if (demoMappingType === 'moorl_pl') {
                this.loadDemoCreator(demoEntityId);
                return;
            }

            this.$super('onDemoEntityChange');
        },

        async loadDemoCreator(entityId) {
            const criteria = new Criteria(1, 1);

            if (entityId) {
                criteria.setIds([entityId]);
            }

            const response = await this.repositoryFactory.create('moorl_pl').search(criteria);
            const demoEntity = response[0];

            this.demoEntityId = demoEntity.id;
            Shopware.Store.get('cmsPage').setCurrentDemoEntity(demoEntity);
        },

        _onPageTypeChange() {



            if (this.page.type === 'parts_list_configurator_detail') {
                this.processCreatorDetailType();
            }

            this.$super('onPageTypeChange');
        },

        _processCreatorDetailType() {


            const creatorDetailBlocks = [
                {
                    type: 'moorl-column-layout-1',
                    elements: [
                        {
                            slot: 'slot-a',
                            config: {},
                        }
                    ],
                },
            ];

            creatorDetailBlocks.forEach(block => {
                const newBlock = this.blockRepository.create();

                block.elements.forEach(el => { el.blockId = newBlock.id; });

                this.processBlock(newBlock, block.type);
                this.processElements(newBlock, block.elements);
            });
        }
    }
});
