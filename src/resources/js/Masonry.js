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

    // Parse "sm:2 md:3 lg:6 xl:6" into an object
    parseDataCols(data) {
        const bpMap = { sm: null, md: null, lg: null, xl: null };
        data.split(' ').forEach(pair => {
            const [key, val] = pair.split(':');
            if (key && val) bpMap[key] = parseInt(val);
        });

        // Fill missing breakpoints by inheriting from smaller ones
        const order = ['sm','md','lg','xl'];
        for (let i = 0; i < order.length; i++) {
            if (bpMap[order[i]] === null) {
                // inherit from previous defined breakpoint
                bpMap[order[i]] = i > 0 ? bpMap[order[i-1]] : 1;
            }
        }

        return bpMap;
    }

    // Determine column count based on current width
    getColumnCount() {
        const width = window.innerWidth;
        if (width >= 1200) return this.breakpoints.xl;
        if (width >= 992)  return this.breakpoints.lg;
        if (width >= 768)  return this.breakpoints.md;
        return this.breakpoints.sm;
    }

    // Create columns dynamically
    setupColumns() {
        this.container.innerHTML = '';
        const colCount = this.getColumnCount();
        this.currentColCount = colCount;

        for (let i = 0; i < colCount; i++) {
            const col = document.createElement('div');
            col.className = 'col flow p-1';
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

        this.processing = false;

    }

    addItem(item) {

        const column = this.getShortestColumn();
        column.appendChild(item);
        this.items.push(item);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                item.classList.add('show');
            });
        });

    }

    // ? NEW METHOD: remove an item cleanly
    removeItem(item) {
        if (!item) return;

        // Optionally animate out
        item.classList.remove('show');
        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        item.style.opacity = '0';
        item.style.transform = 'scale(0.9)';

        // Wait for animation, then remove
        setTimeout(() => {
            if (item.parentElement) item.parentElement.removeChild(item);
            this.items = this.items.filter(i => i !== item);
            this.refreshColumns(true);
        }, 300); // match transition duration
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

    }

}