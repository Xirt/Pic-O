import { AppRequest } from './AppRequest.js';
import { enableVisibilityEvents } from './domHelpers.js';

export class SelectionManager {

    constructor({container, apiUrl}) {

        this.container  = document.getElementById(container);
        this.menu       = this.container.querySelector('.search-select-wrapper');
        this.selectList = this.container.querySelector('.search-select-list');
        this.selectEl   = this.container.querySelector('input[readonly]');
        this.hiddenEl   = this.container.querySelector('input[type="hidden"]');
        this.inputEl    = this.menu.querySelector('input[type="text"]');

        this.optionTpl      = this.container.dataset.tplOption;
        this.noOptionTpl    = this.container.dataset.tplEmpty;
        this.searchEndpoint = apiUrl;

        this.currentItems  = [];
        this.lastQuery     = null;
        this.debounceTimer = null;
        this.activeIndex   = -1;

        this.init();

    }

    init() {

        this.inputEl.addEventListener('input', () => {
            this.updateList();
            this.show();
        });

        this.inputEl.addEventListener('keydown', (e) => {
            this.handleKeyDown(e);
        });

        this.selectEl.addEventListener('focus', () => {
            this.inputEl.focus();
            this.updateList();
            this.show();
        });

        enableVisibilityEvents(this.selectEl);
        this.selectEl.addEventListener('visible', () => {
            this.updateList();
        });

        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hide();
            }
        });

    }

    getOptionFragment(item) {

        const tpl = document.getElementById(this.optionTpl);
        const fragment = tpl.content.cloneNode(true);

        fragment.querySelectorAll('[data-field]').forEach(child => {

            const key = child.dataset.field;
            if (key in item) child.textContent = item[key];

        });

        return fragment;

    }

    getNoOptionFragment() {

        const tpl = document.getElementById(this.noOptionTpl);
        return tpl.content.cloneNode(true);

    }

    populateOptionList(items = []) {

        this.clear();

        if (!items || items.length === 0) {

            const fragment = this.getNoOptionFragment();
            this.selectList.appendChild(fragment);

            return;

        }

        items.forEach((item, index) => {

            const fragment = this.getOptionFragment(item);
            fragment.querySelector('a').addEventListener('click', (e) => {

                this.selectItem(index);
                e.preventDefault();

            });

            this.selectList.appendChild(fragment);
            if (item.id == this.hiddenEl.value) {
                this.updateActiveItem(index);
            }

        });

        this.currentItems = items;

    }

    setItem(id, name) {

        this.inputEl.value  = name;
        this.selectEl.value = name;
        this.hiddenEl.value = id;

        this.updateList();

    }

    selectItem(index) {

        const item = this.currentItems[index];
        if (!item) return;

        this.updateActiveItem(index);
        setTimeout(() => this.updateList(), 500);
        this.hide();

        this.inputEl.value  = this.currentItems[index].name;
        this.selectEl.value = this.currentItems[index].name;
        this.hiddenEl.value = this.currentItems[index].id;



    }

    async fetchItems(query, show = true) {

        try {

            const result = await AppRequest.request(`${this.searchEndpoint}?q=${encodeURIComponent(query)}`, 'GET');
            this.populateOptionList(result.data);

        } catch (e) { console.error(e); }

    }

    updateActiveItem(index) {

        const items = Array.from(this.selectList.children).filter(el => !el.classList.contains('disabled'));
        if (items.length === 0) return;

        items.forEach(el => el.classList.remove('active'));
        items[index].classList.add('active');
        this.activeIndex = index;

    }

    handleKeyDown(e) {

        const items = this.selectList.children.length;
        if (items === 0) return;

        switch (e.key) {

            case 'ArrowDown':

                e.preventDefault();

                this.updateActiveItem((this.activeIndex + 1) % items);
                this.show();

                break;

            case 'ArrowUp':

                e.preventDefault();

                this.updateActiveItem((this.activeIndex - 1 + items) % items);
                this.show();

                break;

            case 'Enter':

                e.preventDefault();

                if (this.activeIndex >= 0) {
                    this.selectItem(this.activeIndex);
                }

                break;

            case 'Escape':

                this.hide();

                break;

        }

    }

    updateList() {

        const query = this.inputEl.value.trim();

        if (query === this.lastQuery) return;
        this.lastQuery = query;

        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.fetchItems(query);
        }, 300);

    }

    clear() {

        this.selectList.innerHTML = '';
        this.currentItems = [];
        this.activeIndex = -1;

    }

    show() {

        this.menu.classList.add('grow');

    }

    hide() {
        
        this.menu.classList.remove('grow');

    }

}
