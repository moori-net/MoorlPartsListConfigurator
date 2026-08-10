import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import HistoryUtil from 'src/utility/history/history.util';

export default class MoorlPartsListConfiguratorPlugin extends Plugin {
    static options = {
        type: 'calculator',
        url: null,
        optionCount: 0,
        loaderClass: 'loader',
        offsetTop: window.moorlOffsetTop ?? 30,
        iconLocked: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>',
        iconComplete: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>'
    };

    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);

        this._filters = {
            options: []
        };

        this._summary = [];
        this._timeout = null;
        this._enableNextStep = true;

        this._previewImage = document.getElementById('previewImage');
        this._mySummaryEl = document.getElementById('mySummary');
        this._partsListEl = document.getElementById('partsList');
        this._accessoryList = document.getElementById('accessoryList');
        this._formEl = this.el.querySelector('form');
        this._loadButton = this.el.querySelector('.js-load-button');

        this._groups = Array.from(
            this._formEl.querySelectorAll('.js-group')
        );

        this._optionGroups = this._groups.filter(
            groupEl => !groupEl.dataset.logical
        );

        this.options.optionCount = this._groups.length;

        this._groups.forEach((groupEl, index) => {
            const stepEl = groupEl.querySelector('[data-step]');

            if (stepEl) {
                stepEl.textContent = index + 1;
            }
        });

        this._setFilterState();
        this._initializeAvailability();
    }

    _initializeAvailability() {
        const params = Object.fromEntries(
            new URLSearchParams(window.location.search).entries()
        );

        const loadGroup = index => {
            const groupEl = this._optionGroups[index];

            if (!groupEl) {
                finish();
                return;
            }

            this._loadAvailability(
                params,
                groupEl,
                availableOptionIds => {
                    const selectionChanged = this._applyAvailability(
                        groupEl,
                        availableOptionIds,
                        true
                    );

                    if (selectionChanged) {
                        this._resetConfiguration();
                        this._initializeAvailability();
                        return;
                    }

                    const checkedOption = groupEl.querySelector(
                        'input[type=radio]:checked'
                    );

                    if (checkedOption) {
                        loadGroup(index + 1);
                        return;
                    }

                    finish();
                }
            );
        };

        const finish = () => {
            this._registerEvents();
            this._refresh('options', true);
        };

        loadGroup(0);
    }

    _resetConfiguration() {
        this._formEl.reset();

        this._formEl.querySelectorAll('input[type=radio]').forEach(el => {
            el.checked = false;
            el.disabled = false;
        });

        this._filters = {
            options: []
        };

        this._summary = [];

        this._updateHistory('');
    }

    _loadAvailability(filters, groupEl, callback) {
        if (!groupEl) {
            callback([]);
            return;
        }

        const availabilityOptions = Array.from(
            groupEl.querySelectorAll('input[type=radio]'),
            el => el.value
        );

        if (!availabilityOptions.length) {
            callback([]);
            return;
        }

        const query = new URLSearchParams(
            this._mapFilters({
                ...filters,
                availabilityOptions
            })
        ).toString();

        this._client.get(
            `${this.options.url}/availability?${query}`,
            response => {
                let availableOptionIds = [];

                try {
                    availableOptionIds = JSON.parse(response);
                } catch (e) {
                    console.error(
                        'Unable to parse configurator availability.',
                        e
                    );
                }

                callback(
                    Array.isArray(availableOptionIds)
                        ? availableOptionIds
                        : []
                );
            }
        );
    }

    _applyAvailability(
        groupEl,
        availableOptionIds,
        clearInvalidSelection = false
    ) {
        if (!groupEl) {
            return false;
        }

        const available = new Set(availableOptionIds);
        let selectionChanged = false;

        groupEl.querySelectorAll('input[type=radio]').forEach(optionEl => {
            const isAvailable = available.has(optionEl.value);

            optionEl.disabled = !isAvailable;

            if (
                clearInvalidSelection &&
                !isAvailable &&
                optionEl.checked
            ) {
                optionEl.checked = false;
                selectionChanged = true;
            }
        });

        return selectionChanged;
    }

    _getNextGroup(currentGroupEl = null) {
        if (!currentGroupEl) {
            return this._optionGroups[0] ?? null;
        }

        const index = this._optionGroups.indexOf(currentGroupEl);

        return index >= 0
            ? this._optionGroups[index + 1] ?? null
            : null;
    }

    _registerEvents() {
        this._formEl.querySelectorAll('input[type=radio]').forEach(el => {
            el.addEventListener('change', () => {
                if (!el.checked) {
                    return;
                }

                this._resetFollowingSteps(el);

                this._refresh(
                    'options',
                    false,
                    el.closest('.js-group')
                );
            });
        });

        this._loadButton.addEventListener('click', () => {
            this._loadHistory();

            this._partsListEl.style.display = '';
            this._loadButton.disabled = true;

            this._loadList(
                this._partsListEl,
                'proxy-cart'
            );
        });
    }

    _registerListEvents(currentEl) {
        currentEl.querySelectorAll('input[type=number]').forEach(el => {
            ['input', 'change'].forEach(eventName => {
                el.addEventListener(eventName, () => {
                    this._refresh('list');
                    this._refreshSummary();
                });
            });
        });
    }

    _resetFollowingSteps(optionEl) {
        const currentGroupEl = optionEl.closest('.js-group');

        if (!currentGroupEl) {
            return;
        }

        let reset = false;

        this._groups.forEach(groupEl => {
            if (reset) {
                groupEl
                    .querySelectorAll('input[type=radio]')
                    .forEach(el => {
                        el.checked = false;
                    });

                groupEl.classList.remove(
                    'configurator-group-complete'
                );

                groupEl.classList.add(
                    'configurator-group-locked'
                );
            }

            if (groupEl === currentGroupEl) {
                reset = true;
            }
        });
    }

    _refresh(
        source,
        availabilityLoaded = false,
        scrollFromGroupEl = null
    ) {
        if (this._timeout) {
            clearTimeout(this._timeout);
            this._timeout = null;
        }

        this._loadHistory();

        this._partsListEl.style.display = 'none';
        this._loadButton.disabled = false;

        this._timeout = setTimeout(() => {
            if (source !== 'options') {
                this._timeout = null;
                return;
            }

            const refresh = () => {
                this._loadList(
                    this._accessoryList,
                    this.options.type === 'calculator'
                        ? 'accessory-list'
                        : 'parts-list'
                );

                this._enableNextStep = true;

                this._groups.forEach((groupEl, index) => {
                    this._loadGroupStep(groupEl, index + 1);
                    this._loadGroupDescription(groupEl);
                    this._loadPreviewImage(groupEl);
                    this._loadLogicalConfigurator(groupEl);
                });

                this._refreshSummary();

                if (scrollFromGroupEl) {
                    this._scrollToNextStep(scrollFromGroupEl);
                }

                this._timeout = null;
            };

            if (availabilityLoaded) {
                refresh();
                return;
            }

            const nextGroupEl = this._getNextGroup(
                scrollFromGroupEl
            );

            if (!nextGroupEl) {
                refresh();
                return;
            }

            this._loadAvailability(
                this._filters,
                nextGroupEl,
                availableOptionIds => {
                    const selectionChanged =
                        this._applyAvailability(
                            nextGroupEl,
                            availableOptionIds,
                            true
                        );

                    if (selectionChanged) {
                        this._loadHistory();
                    }

                    refresh();
                }
            );
        }, 100);
    }

    _scrollToNextStep(currentGroupEl) {
        const nextGroupEl = this._getNextGroup(currentGroupEl);

        if (!nextGroupEl) {
            return;
        }

        window.scrollTo({
            top:
                nextGroupEl.getBoundingClientRect().top +
                window.scrollY -
                Number(this.options.offsetTop + 200),
            behavior: 'smooth'
        });
    }

    _loadList(currentEl, type) {
        if (
            this._filters.options.length < this.options.optionCount ||
            !currentEl
        ) {
            return;
        }

        this.el
            .querySelectorAll('[data-hide-on-load]')
            .forEach(el => {
                el.style.display = '';
            });

        const contentEl =
            currentEl.querySelector('[data-content]') ??
            currentEl;

        contentEl.replaceChildren(
            this._loaderElement()
        );

        const query = new URLSearchParams(
            this._mapFilters(this._filters)
        ).toString();

        this._client.get(
            `${this.options.url}/${type}?${query}`,
            response => {
                contentEl.innerHTML = response;

                window.PluginManager.initializePlugins();

                this._setFilterState();
                this._registerListEvents(contentEl);
                this._refreshSummary();
            }
        );
    }

    _loadGroupStep(groupEl, currentStep) {
        const stepBadge = groupEl.querySelector('[data-step]');

        if (!stepBadge) {
            return;
        }

        groupEl.classList.remove(
            'configurator-group-locked',
            'configurator-group-complete'
        );

        if (!this._enableNextStep) {
            groupEl.classList.add(
                'configurator-group-locked'
            );

            stepBadge.innerHTML = this.options.iconLocked;

            return;
        }

        stepBadge.innerText = currentStep;

        const checkedOption = groupEl.querySelector(
            'input[type=radio]:checked'
        );

        if (!checkedOption) {
            this._enableNextStep = false;
            return;
        }

        groupEl.classList.add(
            'configurator-group-complete'
        );

        stepBadge.innerHTML = this.options.iconComplete;
    }

    _loadGroupDescription(groupEl) {
        const descriptionEl = groupEl.querySelector(
            '.js-group-description'
        );

        if (!descriptionEl) {
            return;
        }

        const optionEl = groupEl.querySelector(
            'input[type=radio]:checked'
        );

        const description =
            optionEl?.dataset.description ?? '';

        if (!description) {
            descriptionEl.innerHTML = '';
            descriptionEl.style.display = 'none';
            return;
        }

        descriptionEl.innerHTML = description.replace(
            '%name%',
            `<strong>${optionEl.dataset.name ?? ''}</strong>`
        );

        descriptionEl.style.display = '';
    }

    _loadPreviewImage(groupEl) {
        if (!groupEl.dataset.preview) {
            return;
        }

        const optionEl = groupEl.querySelector(
            'input[type=radio]:checked'
        );

        if (optionEl?.dataset.preview) {
            this._previewImage.src = optionEl.dataset.preview;
        }
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
            Object.fromEntries(
                new URLSearchParams(window.location.search).entries()
            ),
            {
                options: []
            }
        );

        this._formEl
            .querySelectorAll('input[type=radio]:checked')
            .forEach(el => {
                this._filters.options.push(el.value);
            });

        this._formEl
            .querySelectorAll('input[type=number]')
            .forEach(el => {
                this._filters[el.name] = el.value;
            });

        const query = new URLSearchParams(
            this._mapFilters(this._filters)
        ).toString();

        this._updateHistory(query);
    }

    _setFilterState() {
        const query = Object.fromEntries(
            new URLSearchParams(window.location.search).entries()
        );

        if (Object.keys(query).length) {
            this._setValuesFromUrl(query);
        }
    }

    _setValuesFromUrl(params = {}) {
        for (const [key, value] of Object.entries(params)) {
            if (key === 'options') {
                value
                    .split('|')
                    .filter(Boolean)
                    .forEach(id => {
                        const optionEl = this.el.querySelector(
                            `input[type=radio][value="${id}"]`
                        );

                        if (optionEl) {
                            optionEl.checked = true;
                        }
                    });

                continue;
            }

            const numberEl = this.el.querySelector(
                `input[type=number][name="${key}"]`
            );

            if (numberEl) {
                numberEl.value = value;
            }
        }
    }

    _updateHistory(query) {
        HistoryUtil.push(
            HistoryUtil.getLocation().pathname,
            query,
            {}
        );
    }

    _mapFilters(filters) {
        return Object.fromEntries(
            Object.entries(filters)
                .map(([key, value]) => [
                    key,
                    Array.isArray(value)
                        ? value.join('|')
                        : value
                ])
                .filter(([, value]) => `${value}`.length)
        );
    }

    _loaderElement() {
        const wrapper = document.createElement('div');

        wrapper.classList.add(
            'd-flex',
            'justify-content-center',
            'align-items-center',
            'p-5'
        );

        const loader = document.createElement('span');
        loader.classList.add(this.options.loaderClass);

        wrapper.appendChild(loader);

        return wrapper;
    }

    _refreshSummary() {
        const summary = [];

        this._groups.forEach(groupEl => {
            const checkedOptionEl = groupEl.querySelector(
                'input[type=radio]:checked'
            );

            if (!checkedOptionEl) {
                return;
            }

            summary.push({
                id: groupEl.id,
                group: groupEl.dataset.name ?? '',
                option: checkedOptionEl.dataset.name ?? ''
            });
        });

        this._formEl
            .querySelectorAll('.js-summary-group')
            .forEach(summaryGroupEl => {
                const values = [];

                summaryGroupEl
                    .querySelectorAll('.js-summary-item')
                    .forEach(summaryItemEl => {
                        const inputEl = summaryItemEl.querySelector(
                            'input, select, textarea'
                        );

                        const value = inputEl?.value?.trim();

                        if (!value) {
                            return;
                        }

                        const unit =
                            summaryItemEl.dataset.unit ??
                            summaryGroupEl.dataset.unit ??
                            '';

                        values.push(
                            [
                                summaryItemEl.dataset.name ?? '',
                                [value, unit]
                                    .filter(Boolean)
                                    .join(' ')
                            ]
                                .filter(Boolean)
                                .join(': ')
                        );
                    });

                if (!values.length) {
                    return;
                }

                summary.push({
                    id: summaryGroupEl.id,
                    group: summaryGroupEl.dataset.name ?? '',
                    option: values.join(', ')
                });
            });

        this._summary = summary;

        this._renderSummaryTable(
            this._mySummaryEl?.querySelector('[data-content]')
        );
    }

    _renderSummaryTable(containerEl) {
        if (!containerEl) {
            return;
        }

        const tableEl = document.createElement('table');

        tableEl.classList.add(
            'table',
            'align-middle',
            'mb-0'
        );

        const tableBodyEl = document.createElement('tbody');

        this._summary.forEach(summaryItem => {
            const rowEl = document.createElement('tr');

            const groupCellEl = document.createElement('th');
            groupCellEl.scope = 'row';

            if (summaryItem.id) {
                const groupLinkEl = document.createElement('a');

                groupLinkEl.href =
                    `#${encodeURIComponent(summaryItem.id)}`;

                groupLinkEl.textContent =
                    summaryItem.group ?? '';

                groupLinkEl.classList.add(
                    'text-decoration-none'
                );

                groupCellEl.appendChild(groupLinkEl);
            } else {
                groupCellEl.textContent =
                    summaryItem.group ?? '';
            }

            const optionCellEl = document.createElement('td');

            optionCellEl.textContent =
                summaryItem.option ?? '';

            rowEl.append(
                groupCellEl,
                optionCellEl
            );

            tableBodyEl.appendChild(rowEl);
        });

        tableEl.appendChild(tableBodyEl);
        containerEl.replaceChildren(tableEl);
    }
}
