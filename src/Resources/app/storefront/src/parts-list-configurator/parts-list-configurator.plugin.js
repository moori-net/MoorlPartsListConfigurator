import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import HistoryUtil from 'src/utility/history/history.util';
import querystring from 'query-string';

export default class MoorlPartsListConfiguratorPlugin extends Plugin {
    static options = {
        partsListConfiguratorId: null,
        propertyGroupConfig: [],
        partsListUrl: null,
        logicalConfiguratorUrl: null,
    };

    init() {
        this._urlFilterParams = querystring.parse(HistoryUtil.getSearch());
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._filters = {
            options: []
        };

        this._partsListEl = document.getElementById('partsList');
        this._formEl = this.el.querySelector('form');

        this._setFilterState();
        this._registerEvents();
    }

    _setFilterState() {
        if (Object.keys(this._urlFilterParams).length > 0) {
            this._setValuesFromUrl(this._urlFilterParams);
        }
    }

    _registerEvents() {
        this._formEl.querySelectorAll('.js-group').forEach((groupEl) => {
            groupEl.querySelectorAll('input[type=radio]').forEach((el) => {
                ['keyup', 'change', 'force'].forEach(evt => {
                    el.addEventListener(evt, () => {
                        this._loadLogicalConfigurator(groupEl);

                        this._loadHistory();
                        this._loadPartsList();
                    }, false);
                });
            });
        });
    }

    _registerInputEvents() {
        this._formEl.querySelectorAll('input[type=number]').forEach((el) => {
            ['keyup', 'change', 'force'].forEach(evt => {
                el.addEventListener(evt, () => {
                    this._loadHistory();
                    this._loadPartsList();
                }, false);
            });
        });
    }

    _refreshForm(el) {}

    _buildInputElements(option) {
        const newEl = document.createElement("div");
        const labelEl = document.createElement("label");

        labelEl.innerText = option.name;

        newEl.appendChild(labelEl);

        option.elements.forEach((element) => {
            const inputGroupEl = document.createElement("div");
            const labelEl = document.createElement("span");
            const inputEl = document.createElement("input");
            const appendEl = document.createElement("span");

            inputGroupEl.classList.add('input-group','mb-2');
            labelEl.innerText = element.name;
            labelEl.classList.add('input-group-text');
            inputEl.type = element.type;
            inputEl.name = element.name;
            inputEl.value = this._filters[element.name] ?? '3000';
            inputEl.min = element.min ?? '1000';
            inputEl.step = element.step ?? '500';
            inputEl.classList.add('form-control');
            appendEl.innerText = element.unit ?? 'mm';
            appendEl.classList.add('input-group-text');

            inputGroupEl.appendChild(labelEl);
            inputGroupEl.appendChild(inputEl);
            inputGroupEl.appendChild(appendEl);

            newEl.appendChild(inputGroupEl);
        });

        return newEl;
    }

    _loadPartsList() {
        const mapped = this._mapFilters(this._filters);

        let query = querystring.stringify(mapped);

        this._client.get(this.options.partsListUrl + "?" + query, response => {
            this._partsListEl.innerHTML = response;
            window.PluginManager.initializePlugins();
        });
    }

    _loadLogicalConfigurator(groupEl) {
        if (!groupEl.dataset.logical) {
            return;
        }

        this._filters.group = groupEl.dataset.technicalName;

        const mapped = this._mapFilters(this._filters);
        const logicalConfiguratorEl = groupEl.querySelector('.js-logical-configurator');

        let query = querystring.stringify(mapped);

        this._client.get(this.options.logicalConfiguratorUrl + "?" + query, response => {
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

        this._formEl.querySelectorAll('input[type=number]').forEach((el) => {
            this._filters[el.name] = el.value;
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
