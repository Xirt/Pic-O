import { SelectionToolbar } from './SelectionToolbar.js';

export class Selection {

    constructor(container, toolbar) {

        this.container = container;
        this.active    = false;
        this.selection = new Set();

        this.setupSelectionListener();
        this.setupToolbar();

    }

    setupToolbar() {

        SelectionToolbar.init('.toolbar', {
            onEnter: () => { this.enterSelectionMode(); },
            onExit: () => { this.cancelSelection(); },
        });

    }

    setupSelectionListener() {

        let holdTimer = null;

        this.container.addEventListener('mousedown', (e) => {

            holdTimer = setTimeout(() => {

                const item = e.target.closest('.selectable');
                if (item && this.container.contains(item)) {
                    this.enterSelectionMode();
                }

            }, 400);

            e.preventDefault();

        });

        this.container.addEventListener('mouseup', (e) => {

            clearTimeout(holdTimer);
            if (!this.active) return;

            const item = e.target.closest('.selectable');
            if (!item || !this.container.contains(item)) return;

            const id = item.dataset.id;
            if (this.selection.has(id)) {
                this.selection.delete(id);
                item.classList.remove('selected');
            } else {
                this.selection.add(id);
                item.classList.add('selected');
            }

            SelectionToolbar.update(this.selection.size);

            e.stopPropagation();
            e.preventDefault();

        });

        this.container.addEventListener('click', (e) => {

            if (this.active) {

                e.stopPropagation();
                e.preventDefault();

            }

        });

        this.container.addEventListener('mouseleave', () => clearTimeout(holdTimer));

    }

    enterSelectionMode() {

        this.active = true;
        this.container.parentElement.classList.add('selection-mode');
        SelectionToolbar.show(this.getCount());

        this.container.dispatchEvent(new CustomEvent('selection.start', { }));

    }

    cancelSelection() {

        if (this.active) {

            this.active = false;
            this.selection.clear();
            this.container.parentElement.classList.remove('selection-mode');
            this.container.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
            SelectionToolbar.hide();

        }

        this.container.dispatchEvent(new CustomEvent('selection.stop', { }));

    }

    getSelectedIds() {

        return Array.from(this.selection);

    }

    getCount() {

        return this.selection.size;

    }

}