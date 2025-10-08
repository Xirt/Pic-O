import { blurhash } from './blurhash.js';
import { createIcon } from './domHelpers.js';
import { AppRequest } from './AppRequest.js';

export class Grid {

    constructor(itemManager, container = 'grid', itemSelector = '.grid-item') {

        this.sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

        this.container    = typeof container === 'string'? document.getElementById(container) : container;
        this.itemSelector = itemSelector;
        this.manager      = itemManager;
        this.rendering    = false;
        this.items        = [];

        this.masonry = new Masonry(this.container, {
            itemSelector: itemSelector,
            columnWidth: '.grid-sizer',
            transitionDuration: '0.4s',
            percentPosition: true
        });

    }

    add(items, observe = true) {

        const itemsList = Array.isArray(items) ? items : [items];
        this.items = this.items.concat(itemsList);
        this.render(observe);

    }

    async render(observe) {

        if (!this.rendering) {

            this.rendering = true;

            while (this.items.length) {

                this._append(this.items.shift());
                this.container.dispatchEvent(new CustomEvent('grid.render.added', {}));

                await this.sleep(25);

            }

            if (observe) this.setupObserver();
            this.rendering = false;

            this.container.dispatchEvent(new CustomEvent('grid.render.done', {}));

        }

    }

    _append(element) {

        const wrapper = document.createElement('div');
        wrapper.className = 'col-sm-6 col-lg-4 grid-item overflow-hidden';
        wrapper.appendChild(element);

        this.container.appendChild(wrapper);
        this.masonry.appended(wrapper);

        this.container.dispatchEvent(new CustomEvent('grid.refresh', {
            detail: { item: element }
        }));

    }

    remove(element) {

        this.masonry.remove(element.parentElement);
        this.masonry.layout();

    }

    clear() {

        this.masonry.remove(this.container.children);
        this.masonry.layout();
        this.items = [];

    }

    setupObserver() {

        const items = this.container.querySelectorAll(this.masonry.options.itemSelector);
        if (!items.length) return;

        const observer = new IntersectionObserver(async (entries, obs) => {
            for (const entry of entries) {
                if (entry.isIntersecting && !AppRequest.isLoading()) {
                    obs.disconnect();
                    await this.manager.showMore();
                }
            }
        }, { rootMargin: '200px' });

        observer.observe(items[items.length - 1]);

    }

}

export const GridItemFactory = {

    folder(folder) {

        const card = document.createElement('div');
        card.className = 'card clickable p-0 m-1';

        const body = document.createElement('div');
        body.className = 'card-body text-primary position-relative d-inline-block align-items-center text-center p-4';
        card.appendChild(body);

        const icon = document.createElement('img');
        icon.className = 'img-fluid';
        icon.src = 'images/folder.png';
        body.appendChild(icon);

        const thumb = document.createElement('img');
        thumb.className = 'position-absolute folder-thumbnail w-75';
        thumb.src = `folders/${folder.id}/thumbnail`;
        body.appendChild(thumb);

        const footer = document.createElement('div');
        footer.className = 'card-footer text-center text-truncate';
        footer.textContent = folder.name;
        footer.title = folder.name;
        card.appendChild(footer);

        return card;

    },

    photoFolder(photo, manager) {

        const card = document.createElement('a');
        card.href = photo.path_full;
        card.setAttribute('data-id', photo.id);
        card.setAttribute('data-type', 'image');
        card.className = 'card selectable clickable thumbnail p-0 m-1';

        const body = document.createElement('div');
        body.className = 'card-img-container text-primary position-relative d-inline-block align-items-center text-center p-4';
        card.appendChild(body);

        const thumb = document.createElement('img');
        thumb.className = 'card-img-top';
        thumb.src = photo.path_thumb;
        body.appendChild(thumb);

        const icon = createIcon('check-circle-fill', 'text-light opacity-75 position-absolute top-50 start-50 translate-middle fs-1 pb-4');
        card.appendChild(icon);

        const footer = document.createElement('div');
        footer.className = 'card-footer text-center text-truncate';
        footer.textContent = photo.filename;
        footer.title = photo.filename;
        card.appendChild(footer);

        return card;

    },

    async photo(photo, inAlbum = true) {

        const ratioSmall = Math.min(250 / photo.width, 1);
        const newWidth  = Math.round(photo.width * ratioSmall);
        const newHeight = Math.round(photo.height * ratioSmall);

        const card = document.createElement('a');
        card.id = `card-${photo.id}`;
        card.href = photo.path_full;
        card.setAttribute('data-id', photo.id);
        card.setAttribute('data-type', 'image');
        card.style.cssText = `width: 100%; aspect-ratio: ${newWidth} / ${newHeight};`;
        card.className = 'clickable selectable thumbnail position-relative d-block align-items-center text-center m-1 mb-0';

        const icon = createIcon('check-circle-fill', 'text-light opacity-75 position-absolute top-50 start-50 translate-middle fs-1');
        card.appendChild(icon);

        const body = document.createElement('div');
        body.className = 'img-container text-primary position-relative d-inline-block align-items-center text-center';

        const placeholder = await this._createBlurHash(photo, newWidth, newHeight);
        placeholder.className = 'd-block w-100 h-100 m-0 blurhash';
        placeholder.removeAttribute('height');
        placeholder.removeAttribute('width');
        card.appendChild(placeholder);

        const thumb = document.createElement('img');
        thumb.className = 'd-block w-100 h-100 opacity-0 m-0';
        thumb.src = photo.path_thumb;
        card.appendChild(thumb);

        const toolbar = document.createElement('div');
        toolbar.className = 'd-block card-img-overlay bg-dark text-light fw-semibold quick-actions top p-1 me-1';    // TODO :: me-1 is workaround and should not be required
        card.appendChild(toolbar);

        toolbar.appendChild(this.createInfoButton(card));
        toolbar.appendChild(this.createDownloadButton(card));

        if (inAlbum) {
            toolbar.appendChild(this.createCoverButton(card));
            toolbar.appendChild(this.createDeleteButton(card));
        }

        thumb.onload = () => {
            thumb.classList.remove('opacity-0');
            placeholder.classList.add('opacity-0');
            setTimeout(() => card.removeChild(placeholder), 1000);
        };

        return card;

    },

    async album(album) {

        const card = document.createElement('a');
        card.className = 'card selectable position-relative ratio ratio-4x3 p-0 clickable';
        card.href = route('albums.show', {id: album.id});
        card.setAttribute('data-id', album.id);

        if (album.cover) {

            const canvas = await this._createBlurHash(album.cover);
            canvas.className = 'd-block position-absolute object-fit-cover w-100 h-100';
            card.appendChild(canvas);

            const thumb = document.createElement('img');
            thumb.className = 'd-block object-fit-cover';
            thumb.src = `photos/${album.cover.id}/thumbnail`;
            thumb.loading = 'lazy';
            card.appendChild(thumb);

        }

        const icon = createIcon('check-circle-fill', 'text-light opacity-75 h-auto w-auto position-absolute top-50 start-50 translate-middle fs-1');
        card.appendChild(icon);

        const photoCount = document.createElement('div');
        photoCount.className = 'card-img-overlay bottom badge rounded-pill bg-secondary text-light fw-bold px-2 py-1 m-1';
        photoCount.textContent = album.photos;
        card.appendChild(photoCount);

        const overlay = document.createElement('div');
        overlay.className = 'card-img-overlay top bg-dark text-light fw-semibold p-2';
        overlay.textContent = album.name;
        card.appendChild(overlay);

        const toolbar = document.createElement('div');
        toolbar.className = 'd-block quick-actions p-1';
        card.appendChild(toolbar);

        toolbar.appendChild(this.createModifyButton(card));
        toolbar.appendChild(this.createDeleteButton(card));

        return card;

    },

    separator(title) {

        const separator = document.createElement('h5');
        separator.className = 'text-dark-emphasis p-2 pb-1 mt-4 fw-semibold border-bottom';
        separator.innerText = title;

        return separator;

    },

    createInfoButton(card) {

        const button = document.createElement('button');
        button.className = 'btn btn-light btn-sm me-1 btn-info';

        const icon = createIcon('info-circle-fill', 'text-secondary');
        button.appendChild(icon);

        return button;

    },

    createDownloadButton(card) {

        const button = document.createElement('button');
        button.className = 'btn btn-light btn-sm me-1 btn-download';

        const icon = createIcon('download', 'text-secondary');
        button.appendChild(icon);

        return button;

    },

    createCoverButton(card) {

        const button = document.createElement('button');
        button.className = 'btn btn-light btn-sm me-1 btn-cover no-share';

        const icon = createIcon('star-fill', 'text-secondary');
        button.appendChild(icon);

        return button;

    },

    createModifyButton(card) {

        const button = document.createElement('button');
        button.className = 'btn btn-light btn-sm me-1 btn-modify no-share';

        const icon = createIcon('pencil-fill', 'text-secondary');
        button.appendChild(icon);

        return button;

    },

    createDeleteButton (card) {

        const button = document.createElement('button');
        button.className = 'btn btn-light btn-sm btn-delete no-share';

        const button_icon = createIcon('trash3', 'text-secondary');
        button.appendChild(button_icon);

        return button;

    },

    async _createBlurHash(photo, width = 250, height = 250) {

        let hash = photo.blurhash;

        const fallbackHash = 'UOOGF~WB~qWB~qWB~qWB~qWB~qWB';
        hash = (typeof hash === 'string' && hash.length >= 6) ? hash : fallbackHash;

        const img = await blurhash.getImageDataAsImageWithOnloadPromise(
            blurhash.decode(hash, width, height),
            width, height
        );

        return img;

    }

};