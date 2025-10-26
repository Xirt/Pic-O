import { Masonry } from './Masonry.js';
import { blurhash } from './blurhash.js';
import { createIcon } from './domHelpers.js';
import { AppRequest } from './AppRequest.js';

export class Grid {

    constructor(itemManager, container = 'grid') {

        this.sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

        this.container    = typeof container === 'string' ? document.getElementById(container) : container;
        this.manager      = itemManager;
        this.rendering    = false;
        this.observer     = true;
        this.items        = [];

        this.masonry = new Masonry(this.container);
        
        this.container.addEventListener('grid.complete', () => {
            
            if (this.observer) {
                this.setupObserver();
            }    
            
        });

    }

    add(items) {

        const itemsList = Array.isArray(items) ? items : [items];
        itemsList.forEach((item) => {
            this._append(item);
        });
        
        this.items = this.items.concat(itemsList);

    }

    async render() {

        if (!this.rendering) {

            this.rendering = true;

            while (this.items.length) {
                this._append(this.items.shift());
            }

            this.rendering = false;
            
        }

    }

    _append(element) {

        this.masonry.queueItem(element);

        this.container.dispatchEvent(new CustomEvent('grid.queued_item', {
            detail: { item: element }
        }));

    }

    remove(element) {

        this.masonry.removeItem(element);

    }

    clear() {

        this.masonry.clear();
        this.items = [];

    }

    setupObserver() {

        const items = this.container.querySelectorAll('.grid-item');
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
        card.className = 'grid-item card folder selectable clickable my-1';

        const body = document.createElement('div');
        body.className = 'card-body text-primary position-relative w-100 ratio ratio-4x3 p-3';
        card.appendChild(body);

        const iconWrapper = document.createElement('div');
        iconWrapper.className = 'd-flex align-items-center justify-content-center';

            const icon = document.createElement('img');
            icon.className = 'h-75 w-75';
            icon.src = 'images/folder.png';
            iconWrapper.appendChild(icon);

            const thumb = document.createElement('img');
            thumb.className = 'position-absolute w-75 mt-2';
            thumb.src = `folders/${folder.id}/thumbnail`;
            iconWrapper.appendChild(thumb);

        body.appendChild(iconWrapper);

        const footer = document.createElement('div');
        footer.className = 'card-footer text-center text-truncate overflow-hidden';
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
        card.className = 'grid-item card file selectable clickable thumbnail my-1';

        const body = document.createElement('div');
        body.className = 'card-body text-primary position-relative w-100 ratio ratio-4x3 p-3';
        body.style.backgroundImage = `url(${photo.path_thumb})`;
        body.style.backgroundSize = 'cover';
        card.appendChild(body);

        const iconWrapper = document.createElement('div');
        iconWrapper.className = 'd-flex align-items-center justify-content-center';

            const icon = createIcon('check-circle-fill', 'opacity-75');
            iconWrapper.appendChild(icon);

        body.appendChild(iconWrapper);

        const footer = document.createElement('div');
        footer.className = 'card-footer text-center text-truncate overflow-hidden'
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
        card.className = 'grid-item clickable selectable position-relative thumbnail w-100 p-0 my-1';

        const icon = createIcon('check-circle-fill', 'position-absolute top-50 start-50 translate-middle');
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
        toolbar.className = 'd-block card-img-overlay bg-dark text-light fw-semibold quick-actions top p-1';
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
        card.className = 'grid-item card clickable selectable position-relative rounded-0 ratio ratio-4x3 w-100 p-0 my-1 border-0';
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

        const icon = createIcon('check-circle-fill', 'opacity-75 h-auto w-auto position-absolute top-50 start-50 translate-middle');
        card.appendChild(icon);

        const photoCount = document.createElement('div');
        photoCount.className = 'card-img-overlay bottom badge rounded-pill bg-secondary text-light fw-bold px-2 py-1 m-1 me-2';
        photoCount.textContent = album.photos;
        card.appendChild(photoCount);

        const overlay = document.createElement('div');
        overlay.className = 'card-img-overlay top rounded-0 bg-dark text-light fw-semibold text-truncate p-2';
        overlay.textContent = album.name;
        card.appendChild(overlay);

        const toolbar = document.createElement('div');
        toolbar.className = 'd-block quick-actions p-1 pe-2';
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

        const fallbackHash = 'LFRf8~-;?.OF?Hf8ShkB%vWrIEs9';
        hash = (typeof hash === 'string' && hash.length >= 6) ? hash : fallbackHash;

        const img = await blurhash.getImageDataAsImageWithOnloadPromise(
            blurhash.decode(hash, width, height),
            width, height
        );

        return img;

    }

};