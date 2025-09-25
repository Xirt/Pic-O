export const Filter = {

    searchQuery   : '',
    direction     : 'ASC',
    order         : 'name',
    debounceTimer : null,
    debounceDelay : 300,

    searchInput    : document.getElementById('inp-search'),
    orderItems     : document.querySelectorAll('.dropdown-item[data-order]'),
    directionItems : document.querySelectorAll('.dropdown-item[data-direction]'),

    init(callback) {

        this.callback = callback;

        this.bindSearch();
        this.bindOrdering();
        this.bindDirection();

    },

    bindSearch() {

        this.searchInput.addEventListener('input', () => {

            const query = this.searchInput.value.trim();

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