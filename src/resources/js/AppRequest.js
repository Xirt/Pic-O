import { IndicatorManager } from './IndicatorManager.js';

export const AppRequest = (function () {

    const activeRequests = new Map();

    function getCookie(name) {

        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);

        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }

        return null;

    }

    return {

        isLoading(key = null) {

            return key ? activeRequests.has(key) : activeRequests.size > 0;

        },

        async request(url, method = 'GET', data = null, key = null, showIndicator = true) {

            const requestKey = key || `${method.toUpperCase()}:${url}`;

            if (activeRequests.has(requestKey)) {
                return activeRequests.get(requestKey);
            }

            const promise = (async () => {

                if (showIndicator) IndicatorManager.start();

                const options = {
                    method: method.toUpperCase(),
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                };

                if (!['GET', 'HEAD', 'OPTIONS'].includes(options.method)) {

                    const csrfToken = getCookie('XSRF-TOKEN');
                    if (csrfToken && csrfToken.length > 10) {
                        options.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
                    }

                }

                if (data) {

                    const isForm = data instanceof FormData;
                    options.body = isForm ? data : JSON.stringify(data);
                    if (!isForm) options.headers['Content-Type'] = 'application/json';

                }

                try {

                    let parsed;

                    const response = await fetch(url, options);
                    const contentType = response.headers.get('content-type') || '';

                    if (contentType.includes('application/json')) {

                        try {
                            parsed = await response.json();
                        } catch (e) { console.log(e); }

                    } else {

                        parsed = await response.text();

                    }

                    if (!response.ok) {

                        if (showIndicator) IndicatorManager.error(response.status);

                        const message = (typeof parsed === 'object' && parsed?.message) || data;
                        throw new Error(message || 'Request failed');

                    }

                    return parsed;

                } catch (e) {

                    console.log(e);
                    throw e;

                } finally {

                    activeRequests.delete(requestKey);
                    if (showIndicator) IndicatorManager.stop();

                }

            })();

            activeRequests.set(requestKey, promise);
            return promise;

        }

    };

})();        