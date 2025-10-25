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

export function toggleFullscreen(toggle = null, element = document.documentElement) {

    const isFullscreen = !!document.fullscreenElement;
    toggle = (toggle === null) ? !isFullscreen : toggle;

    var elem = document.documentElement;
    if (elem.requestFullscreen) {
        
        toggle ? elem.requestFullscreen() : document.exitFullscreen();
        document.body.classList.toggle('fullscreen-mode', toggle);

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

export function toast(id, delay = 4000) {

    const instance = new bootstrap.Toast(document.getElementById(id), {
        animation: true,
        autohide: true,
        delay
    });
    instance.show();

    return instance;

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