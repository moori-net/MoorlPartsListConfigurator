import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import HistoryUtil from 'src/utility/history/history.util';
import querystring from 'query-string';

export default class MoorlFenceConfiguratorPlugin extends Plugin {
    static options = {
        fenceConfiguratorId: null,
        debug: false,
        partsListUrl: null
    };

    init() {
        this._urlFilterParams = querystring.parse(HistoryUtil.getSearch());
        console.log(this._urlFilterParams);

        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._filters = {
            options: [],
            postOptions: [],
            logicalOptions: [],
        };

        this._partsListEl = document.getElementById('partsList');

        this._setFilterState();
        this._registerEvents();
    }

    _setFilterState() {
        if (Object.keys(this._urlFilterParams).length > 0) {
            this._setValuesFromUrl(this._urlFilterParams);
        }
    }

    _registerEvents() {
        this.el.querySelectorAll('input[type=radio]').forEach((el) => {
            ['keyup', 'change', 'force'].forEach(evt => {
                    el.addEventListener(evt, () => {
                        this._loadHistory();
                        this._loadPartsList();
                    }, false);
            });
        });
    }

    _loadPartsList() {
        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._client.get(this.el.action + "?" + query, response => {
            this._partsListEl.innerHTML = response;

            window.PluginManager.initializePlugins();
        });
    }

    _loadHistory() {
        this._filters = {
            options: [],
            postOptions: [],
            logicalOptions: [],
        };

        this.el.querySelectorAll('input[type=radio][data-initiator=options]').forEach((el) => {
            if (el.checked) {
                this._filters.options.push(el.value);
            }
        });

        this.el.querySelectorAll('input[type=radio][data-initiator=postOptions]').forEach((el) => {
            if (el.checked) {
                this._filters.postOptions.push(el.value);
            }
        });

        this.el.querySelectorAll('input[type=radio][data-initiator=logicalOptions]').forEach((el) => {
            if (el.checked) {
                this._filters.logicalOptions.push(el.value);
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
