import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import HistoryUtil from 'src/utility/history/history.util';
import querystring from 'query-string';

export default class MoorlFenceConfiguratorPlugin extends Plugin {
    static options = {
        fenceConfiguratorId: null,
        debug: false
    };

    init() {
        this._urlFilterParams = querystring.parse(HistoryUtil.getSearch());
        console.log(this._urlFilterParams);

        this._setFilterState();
        this._registerEvents();
    }

    _setFilterState() {
        if (Object.keys(this._urlFilterParams).length > 0) {
            this._setValuesFromUrl(this._urlFilterParams);
        }
    }

    _registerEvents() {
        this.el.querySelectorAll('input[type=radio][data-initiator=options]').forEach((el) => {
            ['keyup', 'change', 'force'].forEach(evt =>
                el.addEventListener(evt, () => {
                    this._loadVariants();
                }, false)
            );
        });
    }

    _loadVariants() {
        const filters = {
            properties: []
        };

        this.el.querySelectorAll('input[type=radio][data-initiator=options]').forEach((el) => {
            if (el.checked) {
                filters.properties.push(el.value);
            }
        });

        const mapped = this._mapFilters(filters);

        let query = querystring.stringify(mapped);

        this._updateHistory(query);
    }

    _setValuesFromUrl(params = {}) {
        const properties = params['properties'];

        const ids = properties ? properties.split('|') : [];

        ids.forEach(id => {
            const checkboxEl = this.el.querySelector('[value="' + id + '"]');

            if (checkboxEl) {
                checkboxEl.checked = true;
            }
        });
    }

    _updateHistory(query) {
        HistoryUtil.push(HistoryUtil.getLocation().pathname, query, {});
    }

    _mapFilters(filters) {
        const mapped = {};
        Object.keys(filters).forEach((key) => {
            let value = filters[key];

            if (Array.isArray(value)) {
                value = value.join('|');
            }

            const string = `${value}`;
            if (string.length) {
                mapped[key] = value;
            }
        });

        return mapped;
    }
}
