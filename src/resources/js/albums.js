import { History } from './History.js';
import { PicoView } from './PicoView.js';
import { Filter } from './Filter.js';
import { Selection } from './Selection.js';
import { AppRequest } from './AppRequest.js';
import { Grid, GridItemFactory } from './Grid.js';
import { SelectionManager } from './selectionManager.js';
import { populateForm, getJSONFromForm, removeEventListeners, openCanvas, closeCanvas, toast } from './domHelpers.js';

document.addEventListener('DOMContentLoaded', async () => {

    const container = document.getElementById('grid');

    new Selection(container);
    const manager = new Albums();
    manager.init();

    const modifyButton = document.getElementById('modifyButton');
    modifyButton.addEventListener('click', async (e) => {

        const card = document.querySelector('.card.selected');
        manager.showModifyAlbum(card);

        e.preventDefault();

    });


    const deleteButton = document.getElementById('deleteButton');
    deleteButton.addEventListener('click', async (e) => {

        const cards = document.querySelectorAll('.card.selected');
        manager.showRemovalconfirmation(cards);

        e.preventDefault();

    });

    const createForm = document.getElementById('createAlbumForm');
    createForm.addEventListener('submit', async function (e) {

        e.preventDefault();
        if (!createForm.checkValidity()) {
            createForm.reportValidity();
            return;
        }

        try {

            await AppRequest.request(createForm.action, 'POST', getJSONFromForm(createForm), 'albums');
            await manager.init();

        } catch (e) { console.error(e); }

        closeCanvas('offcanvasCreateAlbum');

    });

    const createOffcanvas = document.getElementById('offcanvasCreateAlbum');
    createOffcanvas.addEventListener('show.bs.offcanvas', function () {
        createForm.reset();
    });

    const updateForm = document.getElementById('updateAlbumForm');
    updateForm.addEventListener('submit', async function (e) {

        e.preventDefault();

        try {

            const albumId = document.getElementById('albumId').value;

            await AppRequest.request(route('api.albums.update', { album: albumId }), 'PATCH', getJSONFromForm(updateForm), 'albums');
            await manager.init();

        } catch (e) { console.error(e); }

        closeCanvas('offcanvasUpdateAlbum');

    });

    const folderSelect = new SelectionManager({
        container: 'folderSearchSelect',
        apiUrl: route('api.folders.search')
    });

    createForm.reset();
    folderSelect.updateList();

});

class Albums {

    constructor() {

        Filter.init(() => { this.init(); });

        this.grid   = new Grid(this);
        this.loader = new AlbumsLoader(Filter);

    }

    async init() {

        this.grid.clear();
        await this.show();

    }

    async show() {

        const photos = await this.loader.start();
        this._show(photos);

    }

    async showMore() {

        if (this.loader.hasNextPage()) {

            const photos = await this.loader.next();
            this._show(photos);

        }

    }

    async _show(photos) {

        const elements = await Promise.all(

            photos.map(async (entity) => {

                const el = await GridItemFactory.album(entity);

                const modButton = el.querySelector('.btn-modify');
                modButton.addEventListener('click', (event) => {

                    event.preventDefault();
                    event.stopPropagation();

                    this.showModifyAlbum(el);

                });

                const delButton = el.querySelector('.btn-delete');
                delButton.addEventListener('click', (event) => {

                    event.preventDefault();
                    event.stopPropagation();

                    this.showRemovalconfirmation(el);

                });

                return el;

            })
    
        );

        this.grid.add(elements);

    }

    async showModifyAlbum(card) {

        try {

            const url = route('api.albums.show', { album: card.getAttribute('data-id') });
            const result = await AppRequest.request(url, 'GET');

            populateForm(document.getElementById('updateAlbumForm'), result.data);

        } catch (e) { console.error(e); }

        openCanvas('offcanvasUpdateAlbum');

    }

    showRemovalconfirmation(cards) {

        let cardArray = [cards];
        if (cards instanceof NodeList) {
            cardArray = Array.from(cards);
        }

        const countEl = document.getElementById('delCount');
        countEl.textContent = cardArray.length;

        const offcanvas = openCanvas('offcanvasRemoveAlbum');

        let removeButton = document.getElementById('btn-remove');
        removeEventListeners(removeButton).addEventListener('click', () => {

            try {

                for (const card of cardArray) {

                    const url = route('api.albums.destroy', { album: card.getAttribute('data-id') });
                    AppRequest.request(url, 'DELETE');

                    toast('Album(s) deleted');
                    this.grid.remove(card);

                }

            } catch (e) { console.log(e); }

            offcanvas.hide();

        });

    }

}

class AlbumsLoader {

    constructor(filter) {

        this.filter      = filter;
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

        return this._fetchPage(this.currentPage + 1);

    }

    async _fetchPage(pageNum = 1) {

        if (AppRequest.isLoading('albums')) {
            return [];
        }

        try {

            const filter = this.filter;
            const url    = `/api/albums/search?q=${encodeURIComponent(filter.searchQuery)}&type=${encodeURIComponent(filter.searchType)}&order=${encodeURIComponent(filter.order)}&direction=${filter.direction}&page=${pageNum}`;
            const result = await AppRequest.request(url, 'GET', null, 'albums');

            this.currentPage = result.meta.current_page;
            this.lastPage    = result.meta.last_page;

            return result.data;

        } catch (e) { console.error(e); }

        return [];

    }

}