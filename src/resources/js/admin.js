import { AppRequest } from './AppRequest.js';
import { JobProgressIndicator } from './JobProgressIndicator.js';

document.addEventListener('DOMContentLoaded', () => {

    let messageTimeoutId;

    const scanButton = document.getElementById('scanButton');
    scanButton.addEventListener('click', async (e) => {

        e.preventDefault();

        try {

            const response = await AppRequest.request(route('api.jobs.dispatch'), 'POST', { type: 'TraverseFolderJob' });
            toast('Scan requested');

        } catch (e) { console.log(e); }

    });

    JobProgressIndicator.start((hasJobs) => {
        scanButton.disabled = hasJobs;
    });

    const settingsForm = document.getElementById('settingsForm');
    settingsForm.addEventListener('submit', async (e) => {

        e.preventDefault();

        try {

            const formData = new FormData(settingsForm);
            const data = Object.fromEntries(formData.entries());
            const response = await AppRequest.request(settingsForm.action, 'POST', data);

            showMessage(settingsForm.querySelector('.form-message'), response.message, true);

        } catch (error) {

            const errorMessage = error?.response?.message || error?.message || 'An unknown error occurred.';
            showMessage(settingsForm.querySelector('.form-message'), errorMessage, false);

        }

    });

    const form = document.getElementById('createForm');
    form.addEventListener('submit', async (e) => {

        e.preventDefault();

        try {

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const response = await AppRequest.request(form.action, 'POST', data);

            showMessage(document.getElementById('createFormMessage'), response.message, true);

            setTimeout(() => {
                // TODO :: Dynamically refresh list, reset form and hide canvas
                window.location.href = window.location.href;
            }, 1000);

        } catch (error) {

            const errorMessage = error?.response?.message || error?.message || 'An unknown error occurred.';
            showMessage(document.getElementById('createFormMessage'), errorMessage, false);

        }

    });

    document.querySelectorAll('button[data-bs-target="#offcanvas-modify-user"]').forEach(button => {
        button.addEventListener('click', async function () {
            const userId = this.getAttribute('data-user-id');

            try {
                const result = await AppRequest.request(`/api/users/${userId}`, 'GET');
                const user = result.data;

                document.getElementById('modifyUserId').value = user.id;
                document.getElementById('modifyUserName').value = user.name;
                document.getElementById('modifyUserEmail').value = user.email;
                document.getElementById('modifyUserRole').value = user.role;

                // Show offcanvas
                const offcanvasEl = document.getElementById('offcanvas-modify-user');
                const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                offcanvas.show();

            } catch (err) {
                alert('Failed to load user data: ' + err.message);
            }
        });
    });

    document.getElementById('modifyForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const userId = document.getElementById('modifyUserId').value;
        const form = this;
        const url = `/api/users/${userId}`;
        const data = {
            name: form.name.value,
            email: form.email.value,
            role: form.role.value,
        };

        const msg = document.getElementById('modifyFormMessage');

        try {

            const result = await AppRequest.request(url, 'PUT', data);
            location.reload();

        } catch (error) {

            const errorMessage = error?.response?.message || error?.message || 'An unknown error occurred.';
            showMessage(document.getElementById('modifyFormMessage'), errorMessage, false);

        }
    });

    function showMessage(container, message, isSuccess = true) {

        container.classList.add('visible');
        container.classList.toggle('alert-success', isSuccess);
        container.classList.toggle('alert-warning', !isSuccess);
        container.textContent = message;

        if (messageTimeoutId) {
            clearTimeout(messageTimeoutId);
        }

        messageTimeoutId = setTimeout(() => {
            container.classList.remove('visible');
        }, 3000);

    }

});