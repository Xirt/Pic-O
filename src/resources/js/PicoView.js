import { toggleFullscreen, toggleBodyClass, toast } from './domHelpers.js';
import Panzoom from "@panzoom/panzoom";

export class PicoView extends EventTarget {

    constructor(container, selector = 'a.thumbnail') {

        super();

        if (!(container instanceof HTMLElement)) {
            throw new Error('PicoView: container must be a DOM element.');
        }

        this.container = container;
        this.selector  = selector;

        this.init();
        this.activate();

        this.reset();
        this.enable();
        this.refresh();

    }

    init() {

        this.picoView  = document.getElementById("picoView");

        this.stage     = this.picoView.querySelector('.picoview-stage');
        this.indicator = this.picoView.querySelector('.spinner-overlay');

    }

    activate() {

        this.container.addEventListener('click', e => {

            const candidate = e.target.closest('a');
            if (!candidate || !this.enabled) return;

            const index = this.items.findIndex(item =>
                (item.id && item.id === candidate.dataset.id)
            );

            if (index !== -1) {

                e.preventDefault();
                this.show(index);

            }

        });

        document.addEventListener('keydown', e => {

            if (!this.picoView.classList.contains('show')) return;

            switch (e.key) {

                case 'ArrowLeft':
                    this.prev();
                    break;

                case 'ArrowRight':
                    this.next();
                    break;

                case 'Escape':
                    this.close();
                break;

            }
        });

        const toolbar = document.getElementById('toolbar');
        toolbar.addEventListener('click', e => {

            const btn = e.target.closest('.action');
            if (btn && btn.dataset.action) {

                this.dispatchEvent(new CustomEvent(btn.dataset.action, {
                    detail: { id: this.items[this.currentIndex].id, index: this.currentIndex }
                }));

            }

        });

        const slideshowBtn = document.getElementById('slideshowBtn');
        slideshowBtn.addEventListener('click', () => {
            this.toggleSlideshow();
        });

        const closeBtn = document.getElementById('closeBtn');
        closeBtn.addEventListener('click', () => {
            this.close();
        });

        const prevBtn = document.getElementById('prevBtn');
        prevBtn.addEventListener('click', () => {
            this.prev();
        });

        const nextBtn = document.getElementById('nextBtn');
        nextBtn.addEventListener('click', () => {
            this.next();
        });

    }

    refresh(anchorElements = null) {

        if (!Array.isArray(anchorElements)) {
            anchorElements = this.container.querySelectorAll(this.selector);
        }

        this.items = Array.from(anchorElements, el => ({
            href: el.getAttribute('href'),
            id: el.dataset.id || null,
        }));

    }

    enable() {

        this.enabled = true;

    }

    disable() {

        this.enabled = false;

    }

    reset(items = null) {

        this.slideshowTimer = null;
        this.currentImage = null;
        this.currentIndex = -1;
        this.items = [];

    }

    show(index) {

        const oldImage = this.currentImage;

        if (index < 0 || index >= this.items.length) {
            console.warn('PicoView: Index out of bounds.');
            return;
        }

        this.currentImage = this._getImage(index);
        const transition = this._getTransition(index);

        this._setTransition(this.currentImage, transition);
        this._setTransition(oldImage, transition);
        this.currentIndex = index;

        toggleFullscreen(true);

        this._preloadAdjacent(index);
        this._show(oldImage);

    }

    _toggleLoadingIndicator(toggle) {

        this.indicator.classList.toggle('show', toggle);

    }

    _show(oldImage) {

        this.picoView.classList.remove('d-none');
        this.picoView.offsetHeight;
        this.picoView.classList.add('show');

        const finishShow = () => {
            
            this.dispatchEvent(new CustomEvent('photo.shown', {
                detail: { id: this.items[this.currentIndex].id, index: this.currentIndex }
            }));

            this._toggleLoadingIndicator(false);
            this.currentImage.classList.add('show');
            oldImage?.classList.remove('show');
            oldImage?.classList.add('bye');

            this.oldImage?._panzoom?.destroy();

        };

        const img = this.currentImage.querySelector('img');
        if (img.complete && img.naturalHeight !== 0) {

            finishShow();

        } else {

            this._toggleLoadingIndicator(true);
            img.onload = finishShow;

        }

    }

    _setTransition(el, transition) {

        el?.classList.remove('transition-open', 'transition-left', 'transition-right');
        el?.classList.add(transition);

    }

    _getTransition(futureIndex) {

        if (this.currentIndex == -1) return 'transition-open';
        return futureIndex < this.currentIndex ? 'transition-left' : 'transition-right';

    }

    _getImage(index) {

        const wrapper = document.createElement('div');
        wrapper.classList.add('picoview-img-wrapper', 'overflow-hidden');

        const img = document.createElement('img');
        img.src = this.items[index].href;
        wrapper.appendChild(img);

        this.stage.appendChild(wrapper);

        setTimeout(() => {
            this._activateImage(img, wrapper);
        }, 400);

        return wrapper;

    }

    _activateImage(image, wrapper) {

        let touchStartX = 0;
        let touchEndX   = 0;
        let paused      = false;


        image._panzoom = Panzoom(image, {
            maxScale: 5,
            minScale: 1,
            cursor: 'auto',
            contain: 'outside',
        });

        wrapper.addEventListener("wheel", e => {
            image._panzoom.zoomWithWheel(e);
        });

        wrapper.addEventListener('touchstart', e => {

            touchStartX = e.changedTouches[0].screenX;

        });

        image.addEventListener('panzoomzoom', (event) => {

            const { scale } = event.detail;
            if (Math.abs(scale - 1) < 0.01) {
                image._panzoom.zoom(1);
            }

        });

        wrapper.addEventListener('touchend', e => {

            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;

            const isBaseScale = Math.abs(image._panzoom.getScale() - 1) < 0.01;
            if (!paused && isBaseScale && Math.abs(diff) > 50) {

                paused = true;
                (diff > 0) ? this.prev() : this.next();
                setTimeout(() => paused = false, 300);

            }

        });

    }

    _preloadAdjacent(index) {

        if (index < this.items.length - 1) {
            this._preloadImage(this.items[index + 1].href);
        }

        if (index > 0) {
            this._preloadImage(this.items[index - 1].href);
        }


        if (index === this.items.length - 1) {
            this.dispatchEvent(new CustomEvent('photo.more', {}));
        }

    }

    _preloadImage(href) {

        const img = new Image();
        img.src = href;

    }

    next() {

        if (this.currentIndex < this.items.length - 1) {
            this.show(this.currentIndex + 1);
        }

    }

    prev() {

        if (this.currentIndex > 0) {
            this.show(this.currentIndex - 1);
        }

    }

    close() {

        this.picoView.classList.remove('show');
        toggleFullscreen(false);
        this.stopSlideshow();

        setTimeout(() => {

            this.picoView.classList.add('d-none');
            this.currentImage.remove();
            this.currentIndex = -1;

        }, 400);

    }

    startSlideshow(interval = 4000) {

        toggleBodyClass('slideshow-mode', true);

        this.slideshowTimer = setInterval(() => {
            (this.currentIndex < this.items.length - 1) ? this.next() : this.stopSlideshow();
        }, interval);

        toast('Slideshow started');

    }

    stopSlideshow() {

        if (this.slideshowTimer) {

            toggleBodyClass('slideshow-mode', false);

            clearInterval(this.slideshowTimer);
            this.slideshowTimer = null;

            toast('Slideshow stopped');

        }

    }

    toggleSlideshow(interval = 4000) {

        this.slideshowTimer ? this.stopSlideshow() : this.startSlideshow(interval);

    }

}
