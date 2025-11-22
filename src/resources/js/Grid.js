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

        const tpl = document.querySelector('#card-folder');
        const card = tpl.content.firstElementChild.cloneNode(true);

        const thumb = card.querySelector('.folder-thumb');
        thumb.src = `folders/${folder.id}/thumbnail`;

        const name = card.querySelector('.folder-name');
        name.textContent = folder.name;
        name.title = folder.name;

        return card;

    },

    file(file) {

        const tpl = document.querySelector('#card-file');
        const card = tpl.content.firstElementChild.cloneNode(true);

        card.href = file.path_full;
        card.dataset.id = file.id;

        const body = card.querySelector('.file-body');
        body.style.backgroundImage = `url(${file.path_thumb})`;
        body.style.backgroundSize = 'cover';

        const footer = card.querySelector('.file-name');
        footer.textContent = file.filename;
        footer.title = file.filename;

        return card;

    },

    async album(album) {

        const tpl = document.querySelector('#card-album');
        const card = tpl.content.firstElementChild.cloneNode(true);

        card.href = route('albums.show', { id: album.id });
        card.dataset.id = album.id;

        if (album.cover) {

            const blurCanvas = await this._createBlurHash(album.cover);
            blurCanvas.className = 'd-block position-absolute object-fit-cover w-100 h-100';

            const placeholder = card.querySelector('.album-blurhash');
            placeholder.replaceWith(blurCanvas);

            const thumb = card.querySelector('.album-thumb');
            thumb.src = `photos/${album.cover.id}/thumbnail`;

        } else {

            card.querySelector('.album-blurhash').remove();
            card.querySelector('.album-thumb').remove();

        }

        const photoCount = card.querySelector('.album-photo-count');
        photoCount.textContent = album.photos;

        const title = card.querySelector('.album-title');
        title.textContent = album.display_name;

        return card;

    },

    async photo(photo) {

        const tpl = document.querySelector('#card-photo');
        const card = tpl.content.firstElementChild.cloneNode(true);

        const ratioSmall = Math.min(250 / photo.width, 1);
        const newWidth  = Math.round(photo.width * ratioSmall);
        const newHeight = Math.round(photo.height * ratioSmall);

        card.id = `card-${photo.id}`;
        card.href = photo.path_full;
        card.dataset.id = photo.id;
        card.style.aspectRatio = `${newWidth} / ${newHeight}`;

        // ==== BLURHASH ====
        const blurCanvas = await this._createBlurHash(photo, newWidth, newHeight);
        blurCanvas.className = 'd-block w-100 h-100 m-0 blurhash';

        const placeholder = card.querySelector('.photo-blurhash');
        placeholder.replaceWith(blurCanvas);

        // ==== THUMBNAIL ====
        const thumb = card.querySelector('.photo-thumb');
        thumb.src = photo.path_thumb;

        thumb.onload = () => {
            thumb.classList.remove('opacity-0');
            blurCanvas.classList.add('opacity-0');
            setTimeout(() => blurCanvas.remove(), 1000);
        };

        return card;

    },


    async album2(album) {

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
        overlay.textContent = album.display_name;
        card.appendChild(overlay);

        const toolbar = document.createElement('div');
        toolbar.className = 'd-block quick-actions p-1 pe-2';
        card.appendChild(toolbar);

        toolbar.appendChild(this.createModifyButton(card));
        toolbar.appendChild(this.createDeleteButton(card));

        return card;

    },

    separator(title) {

        const tpl = document.querySelector('#grid-separator');
        const separator = tpl.content.firstElementChild.cloneNode(true);

        separator.textContent = title;

        return separator;

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