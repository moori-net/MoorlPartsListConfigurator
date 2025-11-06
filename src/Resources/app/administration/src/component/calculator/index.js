import template from './index.html.twig';

Shopware.Component.register('moorl-parts-list-calculator', {
    template,

    inject: [
        'foundationApiService',
        'repositoryFactory'
    ],

    props: {
        partsListConfigurator: {
            type: Object,
            required: true
        },
    },

    data() {
        return {
            calculators: [],
            mapping: [],
        };
    },

    computed: {
        calculatorOptions() {
            console.log(this.calculators);
            console.log(Object.keys(this.calculators));


            return Object.keys(this.calculators);
        },

        gridColumns() {
            return  [
                {
                    label: this.$tc('moorl-foundation.field.entity'),
                    property: 'entity',
                    dataIndex: 'entity',
                    primary: true,
                },
                {
                    label: this.$tc('moorl-foundation.field.name'),
                    property: 'name',
                    dataIndex: 'name',
                    primary: true,
                },
                {
                    label: this.$tc('moorl-foundation.field.mapping'),
                    property: 'value',
                    dataIndex: 'value',
                    primary: true,
                    width: '320px',
                }
            ];
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.foundationApiService
                .get('/moorl-pl/get-parts-list-calculators')
                .then((response) => {
                    this.calculators = response;

                    this.refresh();
                });
        },

        refresh() {
            this.initMapping();
        },

        initMapping() {
            this.partsListConfigurator.mapping ??= {};
            this.mapping = [];

            if (!this.partsListConfigurator.calculator || this.calculators.length === 0) {
                return;
            }

            for (const [entity, mapping] of Object.entries(this.calculators[this.partsListConfigurator.calculator])) {
                for (const name of mapping) {
                    this.partsListConfigurator.mapping[name] ??= null;
                    this.mapping.push({entity, name});
                }
            }
        }
    }
});
