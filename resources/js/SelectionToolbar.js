export const SelectionToolbar = (() => {

    let toolbar;

    let selectedCount = 0;
    let selectionMode = false;
    let callbacks     = { onEnter: null, onExit: null };

    function init(selector = '.toolbar', { onEnter = null, onExit = null } = {}) {

        let enterButton, exitButton;
        toolbar           = document.querySelector(selector);
        callbacks.onEnter = onEnter;
        callbacks.onExit  = onExit;

        if ((enterButton = toolbar.querySelector('.select-start'))) {

            enterButton.addEventListener('click', (e) => {

                e.preventDefault();
                show(0);

                if (typeof callbacks.onEnter === 'function') {
                    callbacks.onEnter();
                }

            });

        }

        if ((exitButton = toolbar.querySelector('.select-stop'))) {

            exitButton.addEventListener('click', (e) => {

                e.preventDefault();
                hide();

                if (typeof callbacks.onExit === 'function') {
                    callbacks.onExit();
                }

            });
        }

        refresh();

    }

    function show(count = 0) {

        selectionMode = true;
        selectedCount = count;
        toolbar.classList.add('selection-mode');
        refresh();             

    }

    function hide() {

        selectionMode = false;
        selectedCount = 0;
        toolbar.classList.remove('selection-mode');
        refresh();

    }

    function update(count) {

        selectedCount = count;
        refresh();

    }

    function refresh() {

        const actionButtons = toolbar.querySelectorAll('.select-action');
        actionButtons.forEach((btn) => {
            btn.disabled = selectedCount <= 0;
        });

    }

    return { init, show, hide, update };

})();