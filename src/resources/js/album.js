import { History } from './History.js';
import { PicoView } from './PicoView.js';
import { Selection } from './Selection.js';
import { AppRequest } from './AppRequest.js';
import { Grid, GridItemFactory } from './Grid.js';
import { populateForm, removeEventListeners, openCanvas, toast } from './domHelpers.js';

document.addEventListener('DOMContentLoaded', async () => {

    const container = document.getElementById('grid');
    const id = document.getElementById('album').dataset.albumId;

    new Selection(container);
    const manager = new Album(id);
    const viewer  = new PicoView(container);

    manager.init();
    attachViewerEvents(viewer, manager);
    attachContainerEvents(container, viewer);

    const shareBtn = document.getElementById('generateTokenBtn');
    shareBtn.addEventListener('click', async function (e) {

        e.preventDefault();

        try {

            const albumId = shareBtn.getAttribute('data-album-id');
            const result = await AppRequest.request(route('api.tokens.store'), 'POST', { album_id: albumId });
            console.log(result);

        } catch (e) { console.error(e); }

        closeCanvas('offcanvas-share-album');
        shareForm.reset();

    });

    function attachViewerEvents(viewer, manager) {

        const viewerEvents = {
            'photo.more'      : e => manager.showMore(),
            'photo.info'      : e => manager.viewInfo(e.detail.id),
            'photo.cover'     : e => manager.setCover(e.detail.id),
            'photo.download'  : e => manager.forceDownload(e.detail.id),
            'photo.remove'    : e => { manager.remove(e.detail.id); viewer.next(); }
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

});

class Album {

    constructor(id) {

        this.id     = id;
        this.grid   = new Grid(this);
        this.loader = new PhotoLoader(id);

    }

    async init() {

        this.grid.clear();
        await this.showMore();

    }

    async showMore() {

        if (this.loader.hasNextPage()) {

            const photos = await this.loader.next();

            const elementPromises = photos.map(async (entity) => {

                const el = await GridItemFactory.photo(entity);

                const delButton = el.querySelector('.btn-delete');
                delButton.addEventListener('click', (event) => {

                    event.preventDefault();
                    event.stopPropagation();

                    this.remove(entity.id);

                });

                const infoButton = el.querySelector('.btn-info');
                infoButton.addEventListener('click', (event) => {

                    event.preventDefault();
                    event.stopPropagation();

                    this.viewInfo(entity.id);

                });

                const coverButton = el.querySelector('.btn-cover');
                coverButton.addEventListener('click', (event) => {

                    event.preventDefault();
                    event.stopPropagation();

                    this.setCover(entity.id);

                });

                const downloadButton = el.querySelector('.btn-download');
                downloadButton.addEventListener('click', (event) => {

                    event.preventDefault();
                    event.stopPropagation();

                    this.forceDownload(entity.id);

                });

                return el;

            });

            const elements = await Promise.all(elementPromises);
            this.grid.add(elements);

        }

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

    async setCover(id) {

        try {

            const url = route('api.albums.update', { album: this.id });
            await AppRequest.request(url, 'PATCH', { 'photo_id' : id});
            toast('coverToast');

        } catch (e) { console.error(e); }


    }

    async remove(id) {

        const card = document.getElementById(`card-${id}`);

        try {

             const url = route('api.albums.photos.removeOne', { album: this.id, photo: id });
             await AppRequest.request(url, 'DELETE');

        } catch (e) { console.error(e); }

        const toastEl = toast('removalToast');
        this.grid.remove(card);

        const undoButton = document.getElementById('btn-undo');
        removeEventListeners(undoButton).addEventListener('click', async () => {

            try {

                 const url = route('api.albums.photos.addOne', { album: this.id, photo: id });
                 await AppRequest.request(url, 'PUT');

            } catch (e) { console.error(e); }

            this.grid.add(card);
            toastEl.hide();

        });

    }

}

class PhotoLoader {

    constructor(albumId) {

        this.albumId     = albumId;
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

        if (this.albumId == null || AppRequest.isLoading(this.albumId)) {
            return [];
        }

        try {

            const result = await AppRequest.request(`/api/albums/${this.albumId}/photos?page=${pageNum}`, 'GET', null, this.albumId);

            this.currentPage = result.meta.current_page;
            this.lastPage    = result.meta.last_page;

            return result.data;

        } catch (e) { console.error(e); }

        return [];

    }

}