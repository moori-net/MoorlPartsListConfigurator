import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import HistoryUtil from 'src/utility/history/history.util';
import querystring from 'query-string';

export default class MoorlPartsListConfiguratorPlugin extends Plugin {
    static options = {
        url: null,
        optionCount: 0
    };

    init() {
        this._urlFilterParams = querystring.parse(HistoryUtil.getSearch());
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._filters = {
            options: []
        };

        this._partsListEl = document.getElementById('partsList');
        this._accessoryList = document.getElementById('accessoryList');
        this._formEl = this.el.querySelector('form');

        this._setFilterState();
        this._registerEvents();
        this._refresh();
    }

    _setFilterState() {
        if (Object.keys(this._urlFilterParams).length > 0) {
            this._setValuesFromUrl(this._urlFilterParams);
        }
    }

    _registerEvents() {
        this._formEl.querySelectorAll('input[type=radio]').forEach((el) => {
            ['keyup', 'change', 'force'].forEach(evt => {
                el.addEventListener(evt, () => {this._refresh();}, false);
            });
        });
    }

    _refresh() {
        this._loadHistory();
        this._loadPartsList();
        this._loadAccessoryList();

        this.options.optionCount = 0;

        this._formEl.querySelectorAll('.js-group').forEach((groupEl) => {
            this.options.optionCount++;

            this._loadLogicalConfigurator(groupEl);
        });
    }

    _loadPartsList() {
        if (this._filters.options.length < this.options.optionCount) {
            return;
        }

        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._client.get(this.options.url + "/parts-list?" + query, response => {
            this._partsListEl.innerHTML = response;
            window.PluginManager.initializePlugins();
        });
    }

    _loadAccessoryList() {
        if (this._filters.options.length < this.options.optionCount) {
            return;
        }

        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._client.get(this.options.url + "/accessory-list?" + query, response => {
            this._accessoryList.innerHTML = response;
            window.PluginManager.initializePlugins();
        });
    }

    _loadLogicalConfigurator(groupEl) {
        if (!groupEl.dataset.logical) {
            return;
        }

        if (this._filters.options.length < this.options.optionCount) {
            return;
        }

        this._filters.group = groupEl.dataset.technicalName;

        const mapped = this._mapFilters(this._filters);
        const logicalConfiguratorEl = groupEl.querySelector('.js-logical-configurator');

        let query = querystring.stringify(mapped);

        this._client.get(this.options.url + "/logical-configurator?" + query, response => {
            logicalConfiguratorEl.innerHTML = response;
            window.PluginManager.initializePlugins();
        });
    }

    _loadHistory() {
        this._filters = {
            options: [],
        };

        this._formEl.querySelectorAll('input[type=radio]').forEach((el) => {
            if (el.checked) {
                let initiator = el.dataset.initiator;

                this._filters.options.push(el.value);
            }
        });

        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._updateHistory(query);
    }

    _setValuesFromUrl(params = {}) {
        for (const [key, value] of Object.entries(params)) {
            const ids = value ? value.split('|') : [];

            ids.forEach(id => {
                const checkboxEl = this.el.querySelector('[value="' + id + '"]');

                if (checkboxEl) {
                    checkboxEl.checked = true;
                }
            });
        }
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
