import { AppRequest } from './AppRequest.js';
import { activateToggles } from './domHelpers.js';

document.addEventListener('DOMContentLoaded', () => {

    const REDIRECT_DELAY = 1000;

    const MESSAGE_DELAY  = 3000;

    let messageTimeoutId;

    activateToggles();

    const form = document.getElementById('loginForm');
    form.addEventListener('submit', async (e) => {

        e.preventDefault();

        try {

            await AppRequest.request('/sanctum/csrf-cookie', 'GET');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const response = await AppRequest.request(form.action, 'POST', data);

            showMessage(response.message, true);

            setTimeout(() => {
                window.location.href = response.redirect_to;
            }, REDIRECT_DELAY);

        } catch (e) {

            const errorMessage = e?.response?.message || e?.message || 'An unknown error occurred.';
            showMessage(errorMessage, false);
            console.error(e);

        }

    });

    function showMessage(message, isSuccess = true) {

        const container = document.getElementById('login-message');
        container.classList.toggle('alert-success', isSuccess);
        container.classList.toggle('alert-warning', !isSuccess);
        container.classList.add('visible');
        container.textContent = message;

        clearTimeout(messageTimeoutId);
        messageTimeoutId = setTimeout(() => {
            container.classList.remove('visible');
        }, MESSAGE_DELAY);

    }

});