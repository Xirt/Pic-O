import './bootstrap';
import { AppRequest } from './AppRequest.js';
import { getJSONFromForm, populateForm, openCanvas, closeCanvas, showMessage } from './domHelpers.js';

const REDIRECT_DELAY = 1000;

(() => {

    let userId;

    // Activate "Update User"-functionality (retrieve & show)
    document.querySelectorAll('[data-bs-toggle="offcanvas-update-user"]').forEach(button => {
        button.addEventListener('click', async function () {

            try {

                userId = this.getAttribute('data-user-id');

                // Populate form & show offcanvas
                const result = await AppRequest.request(`/api/users/${userId}`, 'GET');
                populateForm(document.getElementById('updateUserForm'), result.data);

                openCanvas('offcanvas-update-user');

            } catch (e) { console.log(e); }

        });
    });

    // Activate "Update User"-functionality (store & hide)
    const updateUserForm = document.getElementById('updateUserForm');
    updateUserForm.addEventListener('submit', async function (e) {

        e.preventDefault();

        const messageBox = updateUserForm.querySelector('.form-message');

        try {

            // Store user
            const data = getJSONFromForm(updateUserForm);
            const result = await AppRequest.request(`/api/users/${userId}`, 'PUT', data);

            // Show result (success) & hide offcanvas
            showMessage(messageBox, result.message, true, REDIRECT_DELAY);

            setTimeout(() => {
                closeCanvas('offcanvas-update-user');
            }, REDIRECT_DELAY);

        } catch (error) {

            const errorMessage = error?.response?.message || error?.message || 'An unknown error occurred.';
            showMessage(messageBox, errorMessage, false);

        }
    });

})();