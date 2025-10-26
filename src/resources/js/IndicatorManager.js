export const IndicatorManager = (function () {

    let errorOccurred = false;
    let loadingCount  = 0;
    const indicator   = document.getElementById('indicator-loading');

    function updateIndicator() {

        if (indicator) {

            indicator.classList.toggle('show', (loadingCount !== 0 || errorOccurred));
            indicator.classList.toggle('error', errorOccurred);

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