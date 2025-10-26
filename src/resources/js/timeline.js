import { Masonry } from './Masonry.js';
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
        this.currentGrid = null;
        this.grids       = [];

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

    addPhotoGroup() {

        const entries = Object.entries(this.photoGroups);
        if (!entries.length) {
            return;
        }

        const [date, photoGroup] = entries[0];
        delete this.photoGroups[date];

        if (this.currentDate != date) {

            const groupId = `img_${photoGroup[0].id}`;

            this.createNavItem(groupId, photoGroup[0].taken_age);
            this.createGridSeparator(date);
            this.createGrid(groupId);

        }

        this.addPhotoGroupPhotos(this.currentGrid, photoGroup, entries.length == 1);

    }

    async addPhotoGroupPhotos(targetGrid, photoGroup, isLast) {

        const itemPromises = photoGroup.map(async (photo) => {

            const item = await GridItemFactory.photo(photo, false);

            const infoButton = item.querySelector('.btn-info');
            infoButton.addEventListener('click', (event) => {

                event.preventDefault();
                event.stopPropagation();

                this.viewInfo(photo.id);

            });

            const downloadButton = item.querySelector('.btn-download');
            downloadButton.addEventListener('click', (event) => {

                event.preventDefault();
                event.stopPropagation();

                this.forceDownload(photo.id);

            });
        
            return item;
        
        });

        const items = await Promise.all(itemPromises);
        for (const item of items) {
            targetGrid.add(item, isLast);
        }

    }

    createNavItem(link, label) {

        const nav = document.getElementById('dummy-nav');

            const a = document.createElement('a');
            a.textContent = label;
            a.href = `#${link}`;

        nav.appendChild(a);
        
    }

    createGridSeparator(date) {
        
        this.container.append(GridItemFactory.separator(date, this));
        this.currentDate = date;

    }
    
    createGrid(id = '') {
        
        const gridEl = document.createElement("div");
        gridEl.dataset.cols = "sm:3 lg:6";
        gridEl.className = "grid row w-100 g-0";
        this.container.appendChild(gridEl);

        if (id) gridEl.id = id;

        this.masonry = new Masonry(gridEl);
        this.currentGrid = new Grid(this, gridEl);
        this.grids.push(this.currentGrid);

        gridEl.addEventListener("grid.refresh", () => {

            const items = this.grids.flatMap(grid => grid.items);
            viewer.refresh(items);

        });

        gridEl.addEventListener("grid.complete", async () => {

            this.scrollSpy.refresh();
            this.addPhotoGroup();

        });
        
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