import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import HistoryUtil from 'src/utility/history/history.util';

export default class MoorlPartsListConfiguratorPlugin extends Plugin {
    static options = {
        type: 'calculator',
        url: null,
        optionCount: 0,
        loaderClass: 'loader'
    };

    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._filters = {
            options: []
        };
        this._timeout = null;
        this._step = 0;

        this._partsListEl = document.getElementById('partsList');
        this._accessoryList = document.getElementById('accessoryList');
        this._formEl = this.el.querySelector('form');
        this._loadButton = this.el.querySelector('.js-load-button');

        this._formEl.querySelectorAll('.js-group').forEach(groupEl => {
            this.options.optionCount++;

            const stepEl = groupEl.querySelector('[data-step]');
            if (!stepEl) {
                return;
            }
            stepEl.innerText = this.options.optionCount;
        });

        this._setFilterState();
        this._registerEvents();
        this._refresh('options');
    }

    _loaderElement() {
        const wrapper = document.createElement("div");
        wrapper.classList.add(
            "d-flex",
            "justify-content-center",
            "align-items-center",
            "p-5"
        );

        const loader = document.createElement("span");
        loader.classList.add(this.options.loaderClass);

        wrapper.appendChild(loader);
        return wrapper;
    }

    _setFilterState() {
        const query = Object.fromEntries(new URLSearchParams(window.location.search).entries());

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

        this._loadButton.addEventListener('click', () => {
            this._loadHistory();

            this._partsListEl.style.display = "";
            this._loadButton.disabled = true;

            this._loadProxyCart();
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
        if (this._timeout) {
            clearTimeout(this._timeout);
            this._timeout = null;
        }

        this._loadHistory();

        this._partsListEl.style.display = "none";
        this._loadButton.disabled = false;

        this._timeout = setTimeout(() => {
            if (source !== 'options') {
                return;
            }

            if (this.options.type === 'calculator') {
                this._loadAccessoryList();
            } else {
                this._loadPartsList();
            }

            this._formEl.querySelectorAll('.js-group').forEach((groupEl) => {
                this._loadLogicalConfigurator(groupEl);
            });

            this._timeout = null;
        }, 1000);
    }

    _loadList(currentEl, type) {
        if (this._filters.options.length < this.options.optionCount) {
            return;
        }

        this._showHiddenElements();

        const currentContentEl = currentEl.querySelector("[data-content]") ?? currentEl;

        currentContentEl.replaceChildren(this._loaderElement());

        const mapped = this._mapFilters(this._filters);

        let query = new URLSearchParams(mapped).toString()

        this._client.get(this.options.url + "/" + type + "?" + query, response => {
            currentContentEl.innerHTML = response;
            window.PluginManager.initializePlugins();
            this._setFilterState();
            this._registerListEvents(currentContentEl);
        });
    }

    _showHiddenElements() {
        const elements = this.el.querySelectorAll('[data-hide-on-load]');

        elements.forEach(element => {
            element.style.display = "";
        });
    }

    _loadPartsList() {
        this._loadList(this._accessoryList, 'parts-list');
    }

    _loadProxyCart() {
        this._loadList(this._partsListEl, 'proxy-cart');
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
            Object.fromEntries(new URLSearchParams(window.location.search).entries()),
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

        let query = new URLSearchParams(mapped).toString()

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
