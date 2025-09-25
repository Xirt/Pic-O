const IndicatorManager = (function () {
    let errorOccurred = false;
    let loadingCount = 0;
    const indicator = document.getElementById('indicator-loading');

    function updateIndicator() {

        if (indicator) {
            indicator.classList.toggle('d-none', (loadingCount === 0 && !errorOccurred));
            indicator.classList.toggle('text-danger', errorOccurred);
        }

    }

    return {

        start() {
            loadingCount++;
            updateIndicator();
        },

        stop() {

            if (loadingCount > 0) {
                loadingCount--;
                updateIndicator();
            }

        },

        error(status) {

            if (loadingCount > 0 && status >= 500) {
                errorOccurred = true;
                updateIndicator();
            }

        },

        isLoading() {

            return loadingCount > 0 && !errorOccurred;

        },

        reset() {

            loadingCount = 0;
            errorOccurred = false;
            updateIndicator();

        }

    };

})();

export const AppRequest = (function () {

    const activeRequests = new Set();

    return {

        isLoading(key = null) {

            return key ? activeRequests.has(key) : activeRequests.size > 0;

        },

        async request(url, method = 'GET', data = null, key = null) {

            const requestKey = key || `${method.toUpperCase()}:${url}`;

            if (activeRequests.has(requestKey)) {
                console.warn('Request already in progress:', requestKey);
                return;
            }

            activeRequests.add(requestKey);
            IndicatorManager.start();

            const options = {
                method: method.toUpperCase(),
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };

            if (data) {

                const isForm = data instanceof FormData;
                options.body = isForm ? data : JSON.stringify(data);
                if (!isForm) options.headers['Content-Type'] = 'application/json';

            }

            try {

                const response = await fetch(url, options);

                if (!response.ok) {

                    const status = response.status;
                    IndicatorManager.error(status);

                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Request failed');

                }

                const result = await response.json();
                return result;

            } catch (err) {

                console.log(err);
                throw err;

            } finally {

                activeRequests.delete(requestKey);
                IndicatorManager.stop();

            }
        }

    };

})();        