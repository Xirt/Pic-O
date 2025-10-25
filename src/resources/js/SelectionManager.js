import { AppRequest } from './AppRequest.js';

export class SelectionManager {

    constructor({elements, apiUrl, renderItem}) {

        ['input', 'menu', 'container', 'hiddenInput'].forEach(key => {
            this[key] = typeof elements[key] === 'string'
                ? document.getElementById(elements[key])
                : elements[key];
        });

        this.dropdown       = new bootstrap.Dropdown(this.input);
        this.searchEndpoint = apiUrl;
        this.renderItem     = renderItem || this.defaultRenderItem;

        this.currentItems  = [];
        this.selectedValue = null;
        this.lastQuery     = '';
        this.debounceTimer = null;
        this.activeIndex   = -1;

        this.init();

    }

    init() {

        this.input.addEventListener('input', () => this.onInput());
        this.input.addEventListener('keydown', (e) => this.handleKeyDown(e));

        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.dropdown.hide();
            }
        });

    }

    defaultRenderItem(item) {

        const container = document.createElement('div');

        const boldName = document.createElement('b');
        boldName.textContent = item.name;
        container.appendChild(boldName);

        if (item.label) {

            container.appendChild(document.createElement('br'));

            const labelSpan = document.createElement('span');
            labelSpan.style.fontSize = '0.75em';
            labelSpan.textContent = item.label;
            container.appendChild(labelSpan);

        }

        return container;

    }

    clearMenu() {
        this.menu.innerHTML = '';
        this.currentItems = [];
        this.activeIndex = -1;
    }

    populateMenu(items) {

        this.clearMenu();

        if (!items || items.length === 0) {
            const li = document.createElement('li');
            li.innerHTML = `<span class="dropdown-item disabled">No results</span>`;
            this.menu.appendChild(li);
            return;
        }

        items.forEach((item, index) => {
            console.log(item);

            const li = document.createElement('li');
            const content = this.renderItem(item); // Can be HTML string or element

            // If content is a string, wrap it in <a>; if it's a Node, append directly
            if (typeof content === 'string') {
                li.innerHTML = `<a class="dropdown-item h6" href="#" data-value="${item.id}">${content}</a>`;
            } else if (content instanceof Node) {
                const a = document.createElement('a');
                a.className = 'dropdown-item h6';
                a.href = '#';
                //a.dataset.value = item.id;
                a.appendChild(content);
                li.appendChild(a);
            }

            li.querySelector('a').addEventListener('click', (e) => {
                e.preventDefault();
                this.selectItem(index);
            });

            this.menu.appendChild(li);
        });

        this.dropdown.show();
        this.currentItems = items;
    }

    selectItem(index) {

        const item = this.currentItems[index];
        if (!item) return;

        this.input.value = item.label || item.name;
        this.hiddenInput.value = item.id;
        this.selectedValue = item.id;
        this.dropdown.hide();
        this.clearMenu();
    }

    async fetchItems(query) {

        if (!query || query.length < 2) {
            return this.clearMenu();
        }

        try {

            const result = await AppRequest.request(`${this.searchEndpoint}?q=${encodeURIComponent(query)}`, 'GET');
            this.populateMenu(result.data);

        } catch (err) { console.error(e); }

    }

    updateActiveItem(direction) {
        const items = Array.from(this.menu.querySelectorAll('.dropdown-item:not(.disabled)'));
        if (items.length === 0) return;

        items.forEach(el => el.classList.remove('active'));

        if (direction === 'down') {
            this.activeIndex = (this.activeIndex + 1) % items.length;
        } else if (direction === 'up') {
            this.activeIndex = (this.activeIndex - 1 + items.length) % items.length;
        }

        items[this.activeIndex].classList.add('active');
    }

    handleKeyDown(e) {
        if (this.menu.children.length === 0) return;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.updateActiveItem('down');
                this.dropdown.show();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.updateActiveItem('up');
                this.dropdown.show();
                break;
            case 'Enter':
                e.preventDefault();
                if (this.activeIndex >= 0) {
                    this.selectItem(this.activeIndex);
                }
                break;
            case 'Escape':
                this.dropdown.hide();
                break;
        }
    }

    onInput() {
        const query = this.input.value.trim();

        if (query === this.lastQuery) return;
        this.lastQuery = query;

        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.fetchItems(query);
        }, 300);
    }
}
