import { PicoView } from './PicoView.js';
import { Selection } from './Selection.js';
import { AppRequest } from './AppRequest.js';
import { Grid, GridItemFactory } from './Grid.js';
import { SelectionManager } from './selectionManager.js';
import { populateForm, getJSONFromForm, openCanvas } from './domHelpers.js';

document.addEventListener('DOMContentLoaded', async () => {

    const REDIRECT_DELAY = 1000;

    const container = document.getElementById('grid');
    const manager = new Folder(new Selection(container));
    const viewer  = new PicoView(container);

    manager.init();

    attachViewerEvents(viewer, manager);
    attachContainerEvents(container, viewer);
    attachOffcanvasEvents('offcanvas-create-album');
    attachOffcanvasEvents('offcanvas-update-album');

    attachFormEvents('createAlbumForm', 'POST',
        (data) => route('api.albums.store'),
        (result) => route('albums.show', { album: result.data.id })
    );

    attachFormEvents('updateAlbumForm', 'PUT',
        (data) => route('api.album.photos.addMultiple', { album: data.album_id }),
        (result, data) => route('albums.show', { album: data.album_id })
    );

    new SelectionManager({
        elements: {
            input       : 'dropdownInput',
            menu        : 'dropdownMenu',
            container   : 'search-dropdown-container',
            hiddenInput : 'dropdownHidden'
        },
        apiUrl: route('api.albums.search'),
        renderItem: null
    });

    function attachViewerEvents(viewer, manager) {

        const viewerEvents = {
            'photo.more'      : e => manager.showMore(),
            'photo.info'      : e => manager.viewInfo(e.detail.id),
            'photo.download'  : e => manager.forceDownload(e.detail.id)
        };

        for (const [event, handler] of Object.entries(viewerEvents)) {
            viewer.addEventListener(event, handler);
        }

    }

    function attachContainerEvents(container, viewer) {

        const containerEvents = {
            'grid.refresh'    : e => viewer.refresh(),
            'selection.start' : e => viewer.disable(),
            'selection.stop'  : e => viewer.enable()
        };

        Object.entries(containerEvents).forEach(([event, handler]) => {
            container.addEventListener(event, handler);
        });

    }

    function attachOffcanvasEvents(id, inputSelector = '.container-hidden') {

        const offcanvasElement = document.getElementById(id);
        offcanvasElement.addEventListener('show.bs.offcanvas', () => {

            const selectedItems = container.querySelectorAll('.selected');
            const pictures = Array.from(selectedItems).map(el => el.dataset.id);
            const inputElement = offcanvasElement.querySelector(inputSelector);

            inputElement.innerHTML = '';

            pictures.forEach(id => {

                const input = document.createElement('input');
                input.name  = 'pictures';
                input.type  = 'hidden';
                input.value = id;
                inputElement.appendChild(input);

            });

            const countElement = offcanvasElement.querySelector('.picture-count');
            countElement.textContent = pictures.length;

        });

    }

    function attachFormEvents(id, method = 'POST', urlCallback, redirectCallback) {

        const form = document.getElementById(id);
        form.addEventListener('submit', async function (e) {

            e.preventDefault();

            if (!form.checkValidity()) {
                return form.reportValidity();
            }

            try {

                const data = getJSONFromForm(form);
                const result = await AppRequest.request(urlCallback(data), method, data, 'albums');
                form.querySelector('.form-message')?.classList.add('visible');

                setTimeout(() => {
                    window.location.href = redirectCallback(result, data);
                }, REDIRECT_DELAY);

            } catch (e) { console.error(e); }

        });

    }

});

class Folder {

    constructor(selector) {

        this.selector     = selector;

        this.grid         = new Grid(this);
        this.folderLoader = new FolderLoader();
        this.photoLoader  = new PhotoLoader();

    }

    async init() {

        this.loading = false;

        const folder = await this.folderLoader.getFolder();
        this._setParent(folder.parent_id);
        this._setName(folder.name);
        this._setId(folder.id);

        await this.showMore();

    }

    isLoading() {

        return this.loading;

    }

    async showMore() {

        this.loading = true;

        if (this.folderLoader.hasNextPage()) {

            const subfolders = await this.folderLoader.next();
            const elementPromises = subfolders.map((entity) => {

                const el = GridItemFactory.folder(entity, this)
                el.addEventListener('click', async () => {
                    this.setFolder(entity.id);
                });

                return el;

            });

            const elements = await Promise.all(elementPromises);
            this.grid.add(elements);

        }

        if (!this.folderLoader.hasNextPage()) {

            const photos = await this.photoLoader.next();
            const elementPromises = photos.map((entity) => GridItemFactory.photoFolder(entity, this));
            const elements = await Promise.all(elementPromises);
            this.grid.add(elements);

        }

        this.loading = false;

    }

    async viewInfo(id) {

        try {

            const url = route('api.photos.show', { photo: id });
            const result = await AppRequest.request(url, 'GET');

            populateForm(document.getElementById('infoForm'), {
                dimensions: `${result.data.width} x ${result.data.height}`,
                ...result.data,
            });

        } catch (e) { console.error(e); }

        openCanvas('offcanvas-info');

    }

    forceDownload(id) {

        const url = route('photos.download', { photo: id });
        window.location.href = url;

    }

    async setFolder(id) {

        this.selector.cancelSelection();
        this.grid.clear();
        this._setId(id);
        this.init();

    }

    _setName(name) {
        document.getElementById('title').innerHTML = name;
    }

    _setParent(parent_id) {

        if (parent_id != null) {

            const el = GridItemFactory.folder({name: 'Parent', id: parent_id}, this)
            el.addEventListener('click', async () => {
                this.setFolder(parent_id);
            });

            this.grid.add(el);

        }

        this.parent = parent_id;

    }

    _setId(id) {

        this.folderLoader.init(id);
        this.photoLoader.init(id);

    }

}

class FolderLoader {

    constructor() {

        this.folderId    = 0;
        this.lastPage    = null;
        this.currentPage = null;

    }

    async init(folderId) {

        this.folderId    = folderId;
        this.lastPage    = null;
        this.currentPage = 0;

    }

    async start() {

        this.currentPage = 0;
        return await this.next();

    }

    hasNextPage() {

        return (this.lastPage == null || this.currentPage < this.lastPage);

    }

    async next() {

        if (!this.hasNextPage()) {
            return [];
        }

        return await this._fetchPage(this.currentPage + 1);

    }

    async getFolder() {

        try {

            const result = await AppRequest.request(`/api/folders/${this.folderId}`, 'GET', null, this.folderId);

            return result.data;

        } catch (e) { console.error(e); }

        return null;

    }

    async _fetchPage(pageNum = 1) {

        if (this.folderId == null || AppRequest.isLoading(this.folderId)) {
            return [];
        }

        try {

            const result = await AppRequest.request(`/api/folders/${this.folderId}/subfolders?page=${pageNum}`, 'GET', null, this.folderId);

            this.currentPage = result.meta.current_page;
            this.lastPage    = result.meta.last_page;

            return result.data;

        } catch (e) { console.error(e); }

        return [];

    }

}

class PhotoLoader {

    constructor(folderId) {

        this.folderId    = folderId;
        this.lastPage    = null;
        this.currentPage = 0;

    }

    async init(folderId) {

        this.folderId    = folderId;
        this.lastPage    = null;
        this.currentPage = 0;

    }

    async start() {

        this.currentPage = 0;
        return await this.next();

    }

    hasNextPage() {

        return (this.lastPage == null || this.currentPage < this.lastPage);

    }

    async next() {

        if (!this.hasNextPage()) {
            return [];
        }

        return await this._fetchPage(this.currentPage + 1);

    }

    async _fetchPage(pageNum = 1) {

        if (this.folderId == null || AppRequest.isLoading(this.folderId)) {
            return [];
        }

        try {

            const result = await AppRequest.request(`/api/folders/${this.folderId}/photos?page=${pageNum}`, 'GET', null, this.folderId);

            this.currentPage = result.meta.current_page;
            this.lastPage    = result.meta.last_page;

            return result.data;

        } catch (e) { console.error(e); }

        return [];

    }

}