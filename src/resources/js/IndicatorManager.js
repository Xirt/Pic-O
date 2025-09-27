export const IndicatorManager = (function () {

    let errorOccurred = false;
    let loadingCount  = 0;
    const indicator   = document.getElementById('indicator-loading');

    function updateIndicator() {

        if (indicator) {

            indicator.classList.toggle('d-none', (loadingCount === 0 && !errorOccurred));
            indicator.classList.toggle('text-danger', errorOccurred);

        }

    }

    return {

        start: function () {

            loadingCount++;
            updateIndicator();

        },

        stop: function () {
           
            if (loadingCount > 0) {

                loadingCount--;
                updateIndicator();

            }

        },

        error: function () {

            if (loadingCount > 0) {

                errorOccurred = true;
                updateIndicator();

            }

        },

        isLoading: function () {
            return loadingCount > 0 && !errorOccurred;
        },

        reset: function () {

            loadingCount = 0;
            updateIndicator();

        }

    };

})();