export const Filter = {

    searchQuery   : '',
    searchType    : '',
    direction     : 'ASC',
    order         : 'name',
    debounceTimer : null,
    debounceDelay : 300,

    queryEl        : document.getElementById('inp-search'),
    typeEl         : document.getElementById('sel-type'),
    orderItems     : document.querySelectorAll('.dropdown-item[data-order]'),
    directionItems : document.querySelectorAll('.dropdown-item[data-direction]'),

    init(callback) {

        this.callback = callback;

        this.bindType();
        this.bindSearch();
        this.bindOrdering();
        this.bindDirection();

    },

    bindType() {

        this.typeEl.addEventListener('change', () => {

            const query = this.typeEl.value;
            this.searchType = query;
            this.reload();

        });

    },

    bindSearch() {

        this.queryEl.addEventListener('input', () => {

            const query = this.queryEl.value.trim();

            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {

                this.searchQuery = query;
                this.reload();

            }, this.debounceDelay);

        });

    },

    bindOrdering() {

        this.orderItems.forEach(item => {

            item.addEventListener('click', (e) => {

                e.preventDefault();

                const selectedOrder = item.dataset.order;
                if (selectedOrder === this.order)
                    return;

                this.orderItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                this.order = selectedOrder;

                this.reload();

            });

        });

    },

    bindDirection() {

        this.directionItems.forEach(item => {

            item.addEventListener('click', (e) => {

                e.preventDefault();

                const selectedDirection = item.dataset.direction;
                if (selectedDirection === this.direction)
                    return;

                this.directionItems.forEach(i => i.classList.remove('selected'));
                this.direction = selectedDirection;
                item.classList.add('selected');

                this.reload();

            });

        });

    },

    async reload() {

        this.callback();

    }

};