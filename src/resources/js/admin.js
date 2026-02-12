import { AppRequest } from './AppRequest.js';
import { JobProgressIndicator } from './JobProgressIndicator.js';
import { removeEventListeners, openCanvas, closeCanvas, toast, showMessage } from './domHelpers.js';

document.addEventListener('DOMContentLoaded', () => {

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

                const userId   = response.data?.id;
                const userRole = response.data?.role;
                const userName = response.data?.name;

                if (userRole === 'guest') {

                    closeCanvas('offcanvas-create-user');

                    setTimeout(() => {
                        openAlbumManagement(userId, userName);
                    }, 300);

                } else {

                    // TODO :: Check if we can make this list dynamic
                    window.location.href = window.location.href;

                }

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

    document.querySelectorAll('button.remove-user').forEach(button => {
        button.addEventListener('click', function () {

            const userId = this.getAttribute('data-user-id');

            const offcanvas = openCanvas('offcanvasRemoveUser');

            let removeButton = document.getElementById('userRemovalBtn');
            removeEventListeners(removeButton).addEventListener('click', async () => {

                try {

                    const url = route('api.users.destroy', { user: userId });
                    const response = await AppRequest.request(url, 'DELETE');

                    document.querySelector(`tr[data-user-id="${userId}"]`)?.remove();
                    toast('User deleted');

                } catch (e) { if (e.message) toast(e.message); }

                offcanvas.hide();

            });

        });
    });

    // Album Management for Guest Users
    let allAlbums = [];
    let userAssignedAlbums = [];
    let originalAssignedAlbumIds = [];
    let currentUserId = null;

    // Handle "Manage Albums" button click
    document.querySelectorAll('button.manage-albums').forEach(button => {
        button.addEventListener('click', async function () {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            await openAlbumManagement(userId, userName);
        });
    });

    async function openAlbumManagement(userId, userName) {
        currentUserId = userId;
        document.getElementById('album-user-id').value = userId;
        document.getElementById('album-user-name').textContent = userName;

        // Reset state
        allAlbums = [];
        userAssignedAlbums = [];
        originalAssignedAlbumIds = [];

        // Show loading
        const container = document.getElementById('albums-list-container');
        const loadingTpl = document.getElementById('album-loading-tpl');
        container.innerHTML = '';
        container.appendChild(loadingTpl.content.cloneNode(true));

        // Open offcanvas
        const offcanvasEl = document.getElementById('offcanvas-manage-user-albums');
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
        offcanvas.show();

        try {
            // Load all albums (with high per_page for modal display) and user's assigned albums in parallel
            const [albumsResponse, assignedResponse] = await Promise.all([
                AppRequest.request(route('api.albums.search') + '?per_page=9999', 'GET'),
                AppRequest.request(`/api/users/${userId}/albums`, 'GET')
            ]);

            allAlbums = albumsResponse.data || [];
            userAssignedAlbums = assignedResponse.data || [];
            originalAssignedAlbumIds = userAssignedAlbums.map(a => a.id);

            renderAlbumList();
        } catch (error) {
            const errorTpl = document.getElementById('album-error-tpl');
            const errorClone = errorTpl.content.cloneNode(true);
            errorClone.querySelector('.error-message').textContent = `Failed to load albums: ${error.message || 'Unknown error'}`;
            container.innerHTML = '';
            container.appendChild(errorClone);
        }
    }

    function renderAlbumList(searchQuery = '') {
        const container = document.getElementById('albums-list-container');
        const assignedIds = new Set(userAssignedAlbums.map(a => a.id));

        // Filter albums by search query
        let filteredAlbums = allAlbums;
        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            filteredAlbums = allAlbums.filter(album => 
                album.name.toLowerCase().includes(query)
            );
        }

        // Clear container
        container.innerHTML = '';

        // Show empty state if no albums
        if (filteredAlbums.length === 0) {
            const emptyTpl = document.getElementById('album-empty-tpl');
            container.appendChild(emptyTpl.content.cloneNode(true));
            return;
        }

        // Add count header
        const countTpl = document.getElementById('album-count-tpl');
        const countClone = countTpl.content.cloneNode(true);
        countClone.querySelector('.shown-count').textContent = filteredAlbums.length;
        countClone.querySelector('.total-count').textContent = allAlbums.length;
        container.appendChild(countClone);

        // Add album checkboxes
        const checkboxTpl = document.getElementById('album-checkbox-tpl');
        filteredAlbums.forEach(album => {
            const isAssigned = assignedIds.has(album.id);
            const clone = checkboxTpl.content.cloneNode(true);
            
            const wrapper = clone.querySelector('label');
            if (isAssigned) {
                wrapper.classList.add('bg-light');
            }

            const checkbox = clone.querySelector('.album-checkbox');
            checkbox.value = album.id;
            checkbox.checked = isAssigned;

            // Set album thumbnail using cover endpoint
            const img = clone.querySelector('.album-cover');
            img.src = route('albums.cover', { album: album.id });

            clone.querySelector('.album-name').textContent = album.name;
            clone.querySelector('.album-photo-count').textContent = `${album.photos || 0} photos`;

            // Add change listener
            checkbox.addEventListener('change', function () {
                const albumId = parseInt(this.value);
                if (this.checked) {
                    // Add to assigned if not already there
                    if (!userAssignedAlbums.find(a => a.id === albumId)) {
                        const album = allAlbums.find(a => a.id === albumId);
                        if (album) {
                            userAssignedAlbums.push(album);
                        }
                    }
                } else {
                    // Remove from assigned
                    userAssignedAlbums = userAssignedAlbums.filter(a => a.id !== albumId);
                }
            });

            container.appendChild(clone);
        });
    }

    // Search functionality
    const searchInput = document.getElementById('album-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                renderAlbumList(this.value);
            }, 300);
        });
    }

    // Save album assignments
    const saveButton = document.getElementById('save-album-assignments');
    if (saveButton) {
        saveButton.addEventListener('click', async function () {
            const messageContainer = document.getElementById('albumManagementMessage');
            const userId = currentUserId;

            if (!userId) {
                showMessage(messageContainer, 'No user selected', false);
                return;
            }

            try {
                const currentAssignedIds = userAssignedAlbums.map(a => a.id);
                const toAdd = currentAssignedIds.filter(id => !originalAssignedAlbumIds.includes(id));
                const toRemove = originalAssignedAlbumIds.filter(id => !currentAssignedIds.includes(id));

                // Execute changes
                if (toAdd.length > 0) {
                    await AppRequest.request(`/api/users/${userId}/albums`, 'PUT', {
                        album_ids: toAdd
                    });
                }

                if (toRemove.length > 0) {
                    await AppRequest.request(`/api/users/${userId}/albums`, 'DELETE', {
                        album_ids: toRemove
                    });
                }

                showMessage(messageContainer, 'Album assignments saved successfully!', true);
                originalAssignedAlbumIds = [...currentAssignedIds];

                setTimeout(() => {
                    const offcanvasEl = document.getElementById('offcanvas-manage-user-albums');
                    const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                }, 1500);

            } catch (error) {
                const errorMessage = error?.response?.message || error?.message || 'Failed to save changes';
                showMessage(messageContainer, errorMessage, false);
            }
        });
    }

});