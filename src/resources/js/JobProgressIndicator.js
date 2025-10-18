import { AppRequest } from './AppRequest.js';

export const JobProgressIndicator = (function () {

    const POLL_INTERVAL_MS = 1000;

    const container = document.querySelector('.progress-container');

    let previousCounts   = {};
    const jobElements = {};

    let pollTimer       = null;
    let isPolling       = false;
    let checkInProgress = false;
    let statusCallback  = null;

    function init() {

        const elements = container.querySelectorAll('.job-progress');

        elements.forEach((el) => {

            const type = el.dataset.jobType;
            if (!type) return;

            jobElements[type] = {

                jobWrapper: el,
                countEl: el.querySelector('.job-count'),
                upArrow: el.querySelector('.arrow-up'),
                downArrow: el.querySelector('.arrow-down'),
                //barEl: el.querySelector('.progress-bar')

            };

            previousCounts[type] = 0;

        });
    }

    async function getStatus() {

        if (checkInProgress) {
            return;
        }

        checkInProgress = true;

        try {

            const jobs = await AppRequest.request(route('api.jobs.count'), 'GET', null, null, false);

            const currentCounts = {};

            for (const job of jobs) {
                currentCounts[job.type] = job.count;
            }

            update(currentCounts, previousCounts);
            previousCounts = currentCounts;

        } catch (e) { console.log(e); }

        checkInProgress = false;

    }

    function update(currentCounts) {

        let hasJobs = false;

        for (const type in jobElements) {

            const { jobWrapper, countEl, upArrow, downArrow } = jobElements[type];

            const current  = currentCounts[type] || 0;
            const previous = previousCounts[type] || 0;

            countEl.textContent = current;

            if (previous < current) {

                upArrow.classList.add('show');
                setTimeout(() => {
                    upArrow.classList.remove('show');
                }, 500);

            }
            if (previous > current) {

                downArrow.classList.add('show');   
                setTimeout(() => {
                    downArrow.classList.remove('show');
                }, 500);

            }

            hasJobs = (current == 0) ? hasJobs : true;
        }

        container.classList.toggle('visible', hasJobs);
        if (statusCallback) statusCallback(hasJobs);

    }

    return {

        start: function (callback) {

            if (isPolling) return;

            init();
            isPolling = true;

            pollTimer = setInterval(getStatus, POLL_INTERVAL_MS);
            statusCallback = callback || null;
            getStatus();

        },

        stop: function () {

            if (!isPolling) return;

            clearInterval(pollTimer);
            pollTimer = null;
            isPolling = false;

            for (const type in maxCounts) {
                maxCounts[type] = 0;
            }

            update({});

        },

        isRunning: function () {

            return isPolling;

        }

    };

})();