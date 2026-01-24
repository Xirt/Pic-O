const FADEOUT_DELAY = 3000;

export function removeEventListeners(el) {

    const clone = el.cloneNode(true);
    el.parentNode.replaceChild(clone, el);
    return clone;

}

export function createIcon(iconName, extraClasses = "") {

    const el = document.createElement("i");
    el.className = `bi bi-${iconName}`;
    extraClasses && el.classList.add(...extraClasses.split(/\s+/));

    return el;

}

export function toggleBodyClass(className, active = true) {

    document.body.classList.toggle(className, active);

}

export async function toggleFullscreen(toggle = null, element = document.documentElement) {

    const isFullscreen = !!document.fullscreenElement;
    toggle = (toggle === null) ? !isFullscreen : toggle;

    var elem = document.documentElement;
    if (elem.requestFullscreen) {

        try {

            if (toggle && !isFullscreen) {
                await elem.requestFullscreen();
            }

            if (!toggle && isFullscreen) {
                await document.exitFullscreen();
            }

            toggleBodyClass('fullscreen-mode', toggle);

        } catch (e) { console.log(e); }

    }

}

export function getJSONFromForm(form) {

    const data = new FormData(form);
    const json = {};

    for (const [key, value] of data.entries()) {

        if (json.hasOwnProperty(key)) {

            if (!Array.isArray(json[key])) {
                json[key] = [json[key]];
            }

            json[key].push(value);

        } else {

            json[key] = value;

        }

    }

    return json;
}

export function populateForm(form, data) {

    form.reset();
    for (const key in data) {

        if (data.hasOwnProperty(key)) {

            const field = form.elements.namedItem(key);
            if (field && data[key] !== null && typeof data[key] !== 'object') {
                field.value = data[key];
            }

        }

    }

}

export function activateToggles() {

    const toggles = document.querySelectorAll('input[type="checkbox"][data-label-on][data-label-off]');

    toggles.forEach(function (toggle) {

        const label = document.querySelector(`label[for="${toggle.id}"]`);
        if (!label) return;

        function updateLabel() {
            const labelText = toggle.checked ? toggle.dataset.labelOn : toggle.dataset.labelOff;
            label.textContent = labelText;
        }

        toggle.addEventListener('change', updateLabel);
        updateLabel();

    });

}

export function enableVisibilityEvents(element) {

    function isDisplayVisible(el) {
        return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
    }

    let lastVisible = false;
    const intersectionObserver = new IntersectionObserver(entries => {

        entries.forEach(entry => {
            const isVisible = entry.isIntersecting && isDisplayVisible(element);
            updateVisibility(isVisible);
        });

    }, { threshold: 0.01 });

    intersectionObserver.observe(element);

    const mutationObserver = new MutationObserver(() => {
        const isVisible = isDisplayVisible(element);
        updateVisibility(isVisible && isElementInViewport(element));
    });

    mutationObserver.observe(document.body, {
        attributes: true,
        childList: true,
        subtree: true,
        attributeFilter: ['style', 'class', 'hidden']
    });

    function isElementInViewport(el) {

        const rect = el.getBoundingClientRect();

        return (
            rect.width > 0 &&
            rect.height > 0 &&
            rect.bottom >= 0 &&
            rect.right >= 0 &&
            rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.left <= (window.innerWidth || document.documentElement.clientWidth)
        );

    }

    function updateVisibility(isVisible) {

        if (isVisible !== lastVisible) {

            lastVisible = isVisible;
            const eventName = isVisible ? 'visible' : 'hidden';
            element.dispatchEvent(new CustomEvent(eventName, { detail: { visible: isVisible } }));

        }

    }

    updateVisibility(isDisplayVisible(element) && isElementInViewport(element));

    return {

        disconnect() {
            intersectionObserver.disconnect();
            mutationObserver.disconnect();
        }

    };
}

export function toast(message, delay = 2500) {

    const tpl = document.getElementById('toastTpl');
    const fragment = tpl.content.cloneNode(true);

    const toastEl   = fragment.querySelector('.toast');
    const toastBody = toastEl.querySelector('.toast-body');

    document.body.appendChild(fragment);
    toastBody.textContent = message;

    const toast = new bootstrap.Toast(toastEl, {
        animation: true,
        autohide: true,
        delay,
    });

    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => {

        const container = toastEl.closest('.position-fixed');
        if (container) container.remove();

    });

    return toast;
}

let messageTimeoutId;
export function showMessage(container, message, isSuccess = true, fadeOutDelay = null) {

    container.textContent = message;

    // Set requested styling
    container.classList.remove('alert-success', 'alert-warning');
    container.classList.add(isSuccess ? 'alert-success' : 'alert-warning');
    container.classList.add('visible');

    // Fade-out eventually
    clearTimeout(messageTimeoutId);
    messageTimeoutId = setTimeout(() => {
        container.classList.remove('visible');
    }, fadeOutDelay ?? FADEOUT_DELAY);

}

export function openCanvas(id) {

    const instance = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById(id));
    instance.show();

    return instance;

}

export function closeCanvas(id) {

    const instance = bootstrap.Offcanvas.getInstance(document.getElementById(id));
    instance.hide();

    return instance;

}

export function formatDate(isoString) {

    const d     = new Date(isoString);
    const day   = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year  = d.getFullYear();

    return `${day}/${month}/${year}`;

}