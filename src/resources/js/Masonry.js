export class Masonry {

    constructor(container) {

        this.sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

        this.container = container;
        this.processing = false;
        this.queue = [];
        this.items = [];

        this.breakpoints = this.parseDataCols(this.container.dataset.cols || '');
        this.currentColCount = 0;
        this.setupColumns();

        window.addEventListener('resize', () => this.refreshColumns());

    }

    parseDataCols(data) {

        const bpMap = { sm: null, md: null, lg: null, xl: null };
        data.split(' ').forEach(pair => {
            const [key, val] = pair.split(':');
            if (key && val) bpMap[key] = parseInt(val);
        });

        const order = ['sm','md','lg','xl'];
        for (let i = 0; i < order.length; i++) {
            if (bpMap[order[i]] === null) {
                bpMap[order[i]] = i > 0 ? bpMap[order[i - 1]] : 1;
            }
        }

        return bpMap;
    }

    getColumnCount() {

        const width = window.innerWidth;

        switch (true) {

            case width >= 1200:
                return this.breakpoints.xl;

            case width >= 992:
                return this.breakpoints.lg;

            case width >= 768:
                return this.breakpoints.md;
        
        }

        return this.breakpoints.sm;

    }

    setupColumns() {

        this.container.querySelectorAll('.flow').forEach(
            el => el.remove()
        );

        this.currentColCount = this.getColumnCount();
        const colWidthPercent = (100 / this.currentColCount).toFixed(6) + '%';

        for (let i = 0; i < this.currentColCount; i++) {

            const col = document.createElement('div');
            col.className = 'col flow p-1';
            col.style.width = colWidthPercent;
            this.container.appendChild(col);

        }

    }

    refreshColumns(force = false) {
        
        const newCount = this.getColumnCount();
        if (force || newCount !== this.currentColCount) {

            const oldItems = [...this.items];
            this.clear();
            this.setupColumns();
            oldItems.forEach(item => this.addItem(item));

        }

        this.toggleMessageBox();
    }

    queueItem(item) {

        this.queue.push(item);
        this.processQueue();

    }

    async processQueue() {

        if (this.processing) return;

        this.processing = true;

        while (this.queue.length > 0) {

            const item = this.queue.shift();
            this.addItem(item);
            await this.sleep(100);

        }

        this.container.dispatchEvent(new CustomEvent('grid.complete', {}));
        this.processing = false;

    }

    addItem(item) {

        const column = this.getShortestColumn();
        column.appendChild(item);
        this.items.push(item);

        this.container.dispatchEvent(new CustomEvent('grid.refresh', {
			'detail' : this.items
		}));

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                item.classList.add('show');
            });
        });

        this.toggleMessageBox();
    }

    removeItem(item) {

        item.classList.remove('show');

        setTimeout(() => {
            
            if (item.parentElement) item.parentElement.removeChild(item);
            this.items = this.items.filter(i => i !== item);
            this.refreshColumns(true);
            
        }, 300);
    }

    getShortestColumn() {

        const cols = Array.from(this.container.querySelectorAll('.flow'));
        return cols.reduce((shortest, current) =>
            current.scrollHeight < shortest.scrollHeight ? current : shortest
        );

    }

    clear() {

        this.items = [];
        this.queue = [];
        const cols = this.container.querySelectorAll('.flow');
        cols.forEach(col => (col.innerHTML = ''));

        this.toggleMessageBox();

    }

    toggleMessageBox() {

        const box = this.container.querySelector('.empty-grid');
        if (box) box.classList.toggle('d-none', !(this.items.length === 0));
        //if (box) box.classList.toggle('show', !(this.items.length === 0));

    }

}