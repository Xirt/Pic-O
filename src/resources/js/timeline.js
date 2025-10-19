import { PicoView } from './PicoView.js';
import { AppRequest } from './AppRequest.js';
import { Grid, GridItemFactory } from './Grid.js';
import { populateForm, removeEventListeners } from './domHelpers.js';

let viewer;

document.addEventListener('DOMContentLoaded', async () => {

    const manager = new Timeline();
    manager.init();

    const container = document.getElementById('container');
    viewer = new PicoView(container);

    attachViewerEvents(viewer, manager);

    function attachViewerEvents(viewer, manager) {

        const viewerEvents = {
            'photo.more'     : e => manager.showMore(),
            'photo.info'     : e => manager.viewInfo(e.detail.id),
            'photo.download' : e => manager.forceDownload(e.detail.id),
        };

        for (const [event, handler] of Object.entries(viewerEvents)) {
            viewer.addEventListener(event, handler);
        }

    }

});

class Timeline {

    constructor(id = 'container') {

        this.container   = document.getElementById(id);
        this.currentDate = null;
        this.photoGroups = {};

        this.container = document.getElementById('container');
        this.scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#dummy-nav',
        });

        const nav = document.getElementById('currentSectionLabel');
        document.body.addEventListener('activate.bs.scrollspy', (e) => {
            const activeLink = document.querySelector('#dummy-nav a.active');
            if (activeLink) {
                nav.textContent = activeLink.textContent.trim();
            }
        });

        this.loader = new PhotoLoader();
        this.sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    }

    async init() {

        await this.showMore();

    }

    async showMore() {

        if (this.loader.hasNextPage()) {

            const photos = await this.loader.next();
            photos.forEach(photo => {

                if (!this.photoGroups[photo.taken_date]) {
                    this.photoGroups[photo.taken_date] = [];
                }

                this.photoGroups[photo.taken_date].push(photo);

            });

            this.addPhotoGroup();

        }

    }

    async addPhotoGroup() {

        const entries = Object.entries(this.photoGroups);
        if (!entries.length) {
            return;
        }

        const [date, photoGroup] = entries[0];
        delete this.photoGroups[date];

        if (this.currentDate !== date) {

            this.currentDate = date;
            this.container.append(GridItemFactory.separator(date, this));

            const container = this.createGrid(`img_${photoGroup[0].id}`);
            this.grid = new Grid(this, container);
            container.addEventListener("grid.render.added", () => {
                viewer.refresh();
            });

            container.addEventListener("grid.render.done", () => {

                this.scrollSpy.refresh();
                this.addPhotoGroup();

            });

            const nav = document.getElementById('dummy-nav');

            const a = document.createElement('a');
            a.href = `#img_${photoGroup[0].id}`;
            a.textContent = photoGroup[0].taken_age;
            nav.appendChild(a);

        }

        await this.addPhotoGroupPhotos(photoGroup, entries.length == 1);

    }

    async addPhotoGroupPhotos(photoGroup, isLast) {

        for (const photo of [...photoGroup]) {
            const gridItem = await GridItemFactory.photo(photo, false);
            this.grid.add(gridItem, isLast);

            const photoIndex = photoGroup.indexOf(photo);
            if (photoIndex > -1) {
                photoGroup.splice(photoIndex, 1);
            }
        }

    }

    createGrid(id = '') {

        const grid = document.createElement("div");
        grid.className = "grid row w-100 no-gutters";
        if (id) grid.id = id;
        this.container.appendChild(grid);

        const gridSizer = document.createElement("div");
        gridSizer.className = "grid-sizer";
        grid.appendChild(gridSizer);

        const gridGutter = document.createElement("div");
        gridGutter.className = "gutter-sizer";
        grid.appendChild(gridGutter);

        this.grid = grid;

        this.masonry = new Masonry(this.grid, {
            itemSelector: '.grid-item',
            columnWidth: '.grid-sizer',
            transitionDuration: '0.4s',
            percentPosition: true
        });

        return grid;

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

        const offCanvasEl = document.getElementById('offcanvas-info');
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offCanvasEl);
        offcanvas.show();

    }

    forceDownload(id) {

        const url = route('photos.download', { photo: id });
        window.location.href = url;

    }

}

class PhotoLoader {

    constructor() {

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

        if (AppRequest.isLoading("photos")) {
            return [];
        }

        try {

            const result = await AppRequest.request(`/api/photos?page=${pageNum}`, "GET", null, "photos");

            this.currentPage = result.meta.current_page;
            this.lastPage    = result.meta.last_page;

            return result.data;

        } catch (e) { console.error(e); }

        return [];

    }

}