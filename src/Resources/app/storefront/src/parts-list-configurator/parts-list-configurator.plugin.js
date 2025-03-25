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
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._filters = {
            options: []
        };

        this._partsListEl = document.getElementById('partsList');
        this._accessoryList = document.getElementById('accessoryList');
        this._formEl = this.el.querySelector('form');

        this._formEl.querySelectorAll('.js-group').forEach(() => {
            this.options.optionCount++;
        });

        this._setFilterState();
        this._registerEvents();
        this._refresh('options');
    }

    _setFilterState() {
        const query = querystring.parse(HistoryUtil.getSearch())

        if (Object.keys(query).length > 0) {
            this._setValuesFromUrl(query);
        }
    }

    _registerEvents() {
        this._formEl.querySelectorAll('input[type=radio]').forEach((el) => {
            ['keyup', 'change', 'force'].forEach(evt => {
                el.addEventListener(evt, () => {this._refresh('options');}, false);
            });
        });
    }

    _registerListEvents(currentEl) {
        currentEl.querySelectorAll('input[type=number]').forEach((el) => {
            ['change'].forEach(evt => {
                el.addEventListener(evt, () => {this._refresh('list');}, false);
            });
        });
    }

    _refresh(source) {
        this._loadHistory();
        this._loadPartsList();

        if (source !== 'options') {
            return;
        }

        this._loadAccessoryList();

        this._formEl.querySelectorAll('.js-group').forEach((groupEl) => {
            this._loadLogicalConfigurator(groupEl);
        });
    }

    _loadList(currentEl, type) {
        if (this._filters.options.length < this.options.optionCount) {
            return;
        }

        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._client.get(this.options.url + "/" + type + "?" + query, response => {
            currentEl.innerHTML = response;
            window.PluginManager.initializePlugins();
            this._setFilterState();
            this._registerListEvents(currentEl);
        });
    }

    _loadPartsList() {
        this._loadList(this._partsListEl, 'parts-list');
    }

    _loadAccessoryList() {
        this._loadList(this._accessoryList, 'accessory-list');
    }

    _loadLogicalConfigurator(groupEl) {
        if (!groupEl.dataset.logical) {
            return;
        }

        this._filters.group = groupEl.dataset.technicalName;

        this._loadList(
            groupEl.querySelector('.js-logical-configurator'),
            'logical-configurator'
        );
    }

    _loadHistory() {
        this._filters = Object.assign(
            querystring.parse(HistoryUtil.getSearch()),
            {options: []}
        );

        this._formEl.querySelectorAll('input[type=radio]').forEach((el) => {
            if (el.checked) {
                this._filters.options.push(el.value);
            }
        });

        this._formEl.querySelectorAll('input[type=number]').forEach((el) => {
            this._filters[el.name] = el.value;
        });

        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._updateHistory(query);
    }

    _setValuesFromUrl(params = {}) {
        for (const [key, value] of Object.entries(params)) {
            if (key === 'options') {
                const ids = value ? value.split('|') : [];

                ids.forEach(id => {
                    const checkboxEl = this.el.querySelector('input[type=radio][value="' + id + '"]');
                    if (checkboxEl) {checkboxEl.checked = true;}
                });
            } else {
                const numberEl = this.el.querySelector('input[type=number][name="' + key + '"]');
                if (numberEl) {numberEl.value = value;}
            }
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
