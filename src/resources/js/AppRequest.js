import { IndicatorManager } from './IndicatorManager.js';

export const AppRequest = (function () {

    const activeRequests = new Set();

    return {

        isLoading(key = null) {

            return key ? activeRequests.has(key) : activeRequests.size > 0;

        },

        async request(url, method = 'GET', data = null, key = null, showIndicator = true) {

            const requestKey = key || `${method.toUpperCase()}:${url}`;

            if (activeRequests.has(requestKey)) {
                console.warn('Request already in progress:', requestKey);
                return;
            }

            activeRequests.add(requestKey);
            if (showIndicator) IndicatorManager.start();

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
                    if (showIndicator) IndicatorManager.error(status);

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
                if (showIndicator) IndicatorManager.stop();

            }
        }

    };

})();        