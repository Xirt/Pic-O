import { AppRequest } from './AppRequest.js';
import { JobProgressIndicator } from './JobProgressIndicator.js';
import { getJSONFromForm, populateForm, removeEventListeners, openCanvas, closeCanvas, toast, showMessage } from './domHelpers.js';

document.addEventListener('DOMContentLoaded', () => {

    const albumAccessManager = new AlbumAccessManager();
    albumAccessManager.init();

    const userManager = new UserManager();
    userManager.init(albumAccessManager);

    const scanButton = document.getElementById('scanButton');
    scanButton.addEventListener('click', async (e) => {

        e.preventDefault();

        try {

            const response = await AppRequest.request(route('api.jobs.dispatch'), 'POST', { type: 'TraverseFolderJob' });
            toast('Scan requested');

        } catch (e) { console.error(e); }

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

});

class UserManager {

    async init(albumAccessManager) {

        this.albumAccessManager = albumAccessManager;
        this.activateForms();
        await this.list();

    }

    async list() {

        const tpl = document.querySelector('#row-user');
        const userTable = tpl?.parentElement;
        userTable.querySelectorAll('tr').forEach(row => row.remove());

        const response = await AppRequest.request(route('api.users.index'), 'GET');
        (response?.data || []).forEach(userData => {

            const userId = userData.id;
            const userRow = tpl.content.firstElementChild.cloneNode(true);

            userRow.dataset.userId = userId;
            userRow.querySelector('.user-name').textContent = userData.name;
            userRow.querySelector('.user-email').textContent = userData.email;
            userRow.querySelector('.user-role').textContent = userData.role;

            userRow.querySelectorAll('button').forEach(button => {
                button.dataset.userId = userId;
            });

            const manageAlbumsButton = userRow.querySelector('button.manage-albums');
            manageAlbumsButton.hidden = true;

            if (userData.role == "guest") {

                manageAlbumsButton.hidden = false;
                manageAlbumsButton.addEventListener('click', async () => {
                    await this.albumAccessManager.openAlbumManagement(userId, userData.name);
                });

            }

            userTable.appendChild(userRow);

        });

        this.activateList();

    }

    activateForms() {

        const userManager = this;

        const createUserForm = document.getElementById('createUserForm')
        createUserForm?.addEventListener('submit', async function (e) {

            e.preventDefault();
            if (!createUserForm.checkValidity()) {
                return createUserForm.reportValidity();
            }

            createUserForm.querySelectorAll('button').forEach(
                btn => btn.disabled = true
            );

            try {

                const response = await AppRequest.request(createUserForm.action, 'POST', getJSONFromForm(createUserForm), 'users');
                await userManager.list();
                toast(response.message);

                if (response.data?.role === 'guest') {

                    setTimeout(() => {
                        userManager.albumAccessManager.openAlbumManagement(response.data.id);
                    }, 300);

                }

                closeCanvas('offcanvasCreateUser');
                createUserForm.reset();

            } catch (e) {

                const errorMessage = e?.message || 'An unknown error occurred.';
                showMessage(createUserForm.querySelector('.form-message'), errorMessage, false);

            }

            createUserForm.querySelectorAll('button').forEach(
                btn => btn.disabled = false
            );

        });

        const updateUserForm = document.getElementById('updateUserForm');
        updateUserForm?.addEventListener('submit', async function (e) {

            e.preventDefault();

            updateUserForm.querySelectorAll('button').forEach(
                btn => btn.disabled = true
            );

            try {

                const userId = document.getElementById('userId').value;
                const response = await AppRequest.request(route('api.users.update',
                    {user: userId }), 'PATCH', getJSONFromForm(updateUserForm), 'users'
                );

                await userManager.list();
                toast(response.message);

                closeCanvas('offcanvasUpdateUser');

            } catch (e) { console.error(e); }

            updateUserForm.querySelectorAll('button').forEach(
                btn => btn.disabled = false
            );

        });

        createUserForm?.reset();
        updateUserForm?.reset();

    }

    activateList() {

        document.querySelectorAll('button[data-bs-target="#offcanvasUpdateUser"]').forEach(button => {

            button.addEventListener('click', async function () {

                try {

                    const url = route('api.users.show', { user: this.getAttribute('data-user-id') });
                    const result = await AppRequest.request(url, 'GET');
                    populateForm(document.getElementById('updateUserForm'), result.data);

                } catch (e) {

                    console.error(e);

                }

                openCanvas('offcanvasUpdateUser');

            });

        });

        document.querySelectorAll('button.remove-user').forEach(button => {

            button.addEventListener('click', function () {

                const userId = this.getAttribute('data-user-id');
                const offcanvas = openCanvas('offcanvasRemoveUser');

                const removeButton = document.getElementById('userRemovalBtn');
                removeEventListeners(removeButton).addEventListener('click', async () => {

                    try {

                        const url = route('api.users.destroy', { user: userId });
                        const response = await AppRequest.request(url, 'DELETE');

                        document.querySelector(`tr[data-user-id="${userId}"]`)?.remove();
                        toast(response.message);

                    } catch (e) { console.error(e); }

                    offcanvas.hide();

                });

            });

        });

    }

}

class AlbumAccessManager {

    userAssignedAlbumIds = [];
    currentUserId = null;
    emptyBoxTimer = null;

    init() {

        this.query = '';

        const el = document.getElementById('offcanvasManageUserAlbums');
        this.container = bootstrap.Offcanvas.getOrCreateInstance(el);

        this.listContainer = document.getElementById('albums-list-container');
        this.messageBox = this.listContainer.querySelector('.empty-grid');
        this.searchInp = document.getElementById('album-search');

        this.initAssignmentStorage();
        this.initAlbumSearch();
        this.initPagination();
    }

    initAssignmentStorage() {

        const saveButton = document.getElementById('save-assignments');
        saveButton.addEventListener('click', async () => {

            try {

                const response = await AppRequest.request(
                    route('api.users.albums.assignMultiple', { user: this.currentUserId }), 'PUT',
                    { album_ids: this.userAssignedAlbumIds }
                );

                toast(response.message);
                this.container.hide();

            } catch (e) { console.error(e); }

        });
    }

    initAlbumSearch() {

        let searchTimeout;

        this.clearSearch();
        this.searchInp.addEventListener('input', () => {

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.loadAlbumPage(1, this.searchInp.value);
            }, 300);

        });

    }

    clearSearch() {

        this.searchInp.value = '';
        this.query = '';

    }

    initPagination() {

        this.prevBtn = document.getElementById('prev-albums-page');
        this.prevBtn.addEventListener('click', async () => {
            await this.loadAlbumPage(this.currentPage - 1);
        });

        this.nextBtn = document.getElementById('next-albums-page');
        this.nextBtn.addEventListener('click', async () => {
            await this.loadAlbumPage(this.currentPage + 1);
        });

    }

    async openAlbumManagement(userId) {

        this.currentUserId = userId;
        document.getElementById('album-user-id').value = userId; // needed?

        this.clearSearch();
        this.emptyAlbumList();
        this.container.show();

        try {

            const [userResponse, albumsResponse] = await Promise.all([
                AppRequest.request(route('api.users.show', { user: userId })),
                AppRequest.request(route('api.users.albums.index', { user: userId }))
            ]);

            const userAssignedAlbums = albumsResponse.data || [];
            this.userAssignedAlbumIds = userAssignedAlbums.map(a => a.id);

            const userName = userResponse?.data?.name ?? '';
            document.getElementById('album-user-name').textContent = userName;

            this.loadAlbumPage(1);

        } catch (e) { console.error(e); }

    }

    async loadAlbumPage(page, query) {

        this.currentPage = page ?? 1;
        this.query = query ?? this.query ?? '';

        try {

            const response = await AppRequest.request(
                `${route('api.albums.search')}?${new URLSearchParams({
                    q: this.query,
                    page: String(this.currentPage)
                })}`
            );

            this.renderAlbumList(response.data || []);
            this.pagination(response.meta || []);

        } catch (e) { console.error(e); }

    }

    renderAlbumList(albums) {

        this.emptyAlbumList();

        if (albums.length === 0) {

            this.toggleMessageBox(false);
            return;

        }


        const userAlbum = document.getElementById('user-album');
        albums.forEach(album => {

            const isAssigned = this.userAssignedAlbumIds.includes(album.id);
            const clone = userAlbum.content.cloneNode(true);

            const wrapper = clone.querySelector('label');
            if (isAssigned) {
                wrapper.classList.add('bg-light');
            }

            const inpCheckBox = clone.querySelector('.album-checkbox');
            inpCheckBox.value = album.id;
            inpCheckBox.checked = isAssigned;

            const img = clone.querySelector('.album-cover');
            img.src = route('albums.cover', { album: album.id });

            clone.querySelector('.album-name').textContent = album.display_name;
            clone.querySelector('.album-photo-count').textContent = `${album.photos || 0} photos`;

            inpCheckBox.addEventListener('change', (e) => {

                const albumId = parseInt(e.target.value);

                if (e.target.checked) {

                    if (!this.userAssignedAlbumIds.includes(albumId)) {
                        this.userAssignedAlbumIds.push(albumId);
                    }

                } else {

                    this.userAssignedAlbumIds = this.userAssignedAlbumIds.filter(id => id !== albumId);

                }

            });

            this.listContainer.appendChild(clone);
            this.toggleMessageBox(true);

        });

    }

    emptyAlbumList() {

        const albumContainers = this.listContainer.querySelectorAll('.album-selector');
        albumContainers.forEach(container => container.remove());

        this.toggleMessageBox(true);
        this.pagination();

    }

    pagination(albumsMeta = false) {

        this.prevBtn = document.getElementById('prev-albums-page');
        this.nextBtn = document.getElementById('next-albums-page');
        const pageInfo = document.getElementById('album-page-info');

        if (albumsMeta) {
            pageInfo.textContent = `Page ${albumsMeta.current_page} of ${albumsMeta.last_page}`;
        }

        this.prevBtn.disabled = !albumsMeta || albumsMeta.current_page <= 1;
        this.nextBtn.disabled = !albumsMeta || albumsMeta.current_page >= albumsMeta.last_page;

    }

    toggleMessageBox(hidden) {

        if (!hidden) {

            this.messageBox.classList.remove('d-none');
            this.emptyBoxTimer = setTimeout(() => {
                this.messageBox.classList.add('show');
                this.emptyBoxTimer = null;
            }, 400);

        } else if (hidden) {

            this.messageBox.classList.remove('show');
            this.messageBox.offsetHeight;
            this.messageBox.classList.add('d-none');

            clearTimeout(this.emptyBoxTimer);
            this.emptyBoxTimer = null;

        }

    }

}
