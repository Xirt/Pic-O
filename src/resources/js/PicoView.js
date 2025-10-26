import { toggleFullscreen, toast } from './domHelpers.js';

export class PicoView extends EventTarget {

    constructor(container) {

        super();

        this.container = container;
        this.picoView = document.getElementById('picoView');
        this.picoViewInner = document.getElementById('picoViewInner');
        this.closeBtn = document.getElementById('closeBtn');
        this.slideshowBtn = document.getElementById('slideshowBtn');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.toolbar = document.getElementById('toolbar');
        this.spinner = document.getElementById('spinner');

        this.gallery = Array.from(this.container.querySelectorAll('a.thumbnail'));
        this.currentIndex = 0;
        this.currentWrapper = null;
        this.keysEnabled = true;
        this.slideshowTimer = null;
        this.enabled = true;

        this.initEvents();

    }

    initEvents() {

        this.container.addEventListener('click', e => {

            const aTag = e.target.closest('a');
            if (aTag && this.gallery.includes(aTag) && this.enabled) {

                e.preventDefault();
                const index = this.gallery.indexOf(aTag);
                this.show(index);

            }

        });

        this.slideshowBtn.addEventListener('click', () => this.toggleSlideshow());
        this.closeBtn.addEventListener('click', () => this.close());
        this.prevBtn.addEventListener('click', () => this.prev());
        this.nextBtn.addEventListener('click', () => this.next());

        this.toolbar.addEventListener('click', e => {

            const btn = e.target.closest('.action');
            if (btn && btn.dataset.action) {

                this.dispatchEvent(new CustomEvent(btn.dataset.action, {
                    detail: { id: this.gallery[this.currentIndex].dataset.id, index: this.currentIndex }
                }));

            }

        });

        document.addEventListener('keydown', e => {

            if (!this.keysEnabled || !this.picoView.classList.contains('show-bg')) {
                return;
            }

            if (e.key === 'Escape') {
                this.close();
            }

            if (e.key === 'ArrowLeft' && this.currentIndex > 0) {
                this.prev();
            }

            if (e.key === 'ArrowRight' && this.currentIndex < this.gallery.length - 1) {
                this.next();
            }

        });

        let startX = 0, startY = 0;
        this.picoView.addEventListener('touchstart', e => {
            const t = e.changedTouches[0]; startX = t.screenX; startY = t.screenY;
        }, { passive:true });

        this.picoView.addEventListener('touchend', e => {

            const t = e.changedTouches[0];
            const dx = t.screenX - startX, dy = t.screenY - startY;

            if (Math.abs(dx) > Math.abs(dy)) {

                if (Math.abs(dx) > 50) {
                    dx > 0 ? this.prev() : this.next();
                }

            } else if (dy > 50) {

                this.close();

            }

        }, { passive:true });

    }

    enable() {
        this.enabled = true;
    }

    disable() {
        this.enabled = false;
    }

    refresh(items = null) {

        this.gallery = items;
        if (!Array.isArray(this.gallery)) {
            this.gallery = Array.from(this.container.querySelectorAll('a.thumbnail'));
        }

        this.updateNavButtons();

    }

    reset() {

        this.close();
        this.refresh();

    }

    show (index, direction = null) {

        const images = this.gallery[index];
        if (!images) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'picoview-img-wrapper';
        wrapper.classList.add(!direction ? 'open' : direction === 'next' ? 'next' : 'prev');

        const img = document.createElement('img');
        img.src = images.href;
        wrapper.appendChild(img);

        const oldWrapper = this.currentWrapper;
        this.currentWrapper = wrapper;
        this.currentIndex = index;

        this.picoViewInner.appendChild(wrapper);
        this.spinner.classList.add('show');
        this.picoView.classList.remove('d-none');
        this.picoView.classList.add('show-bg');
        this.picoView.offsetHeight;

        img.onload = () => {

            this.spinner.classList.remove('show');
            wrapper.offsetHeight;

            requestAnimationFrame(() => {

                if (!direction) {

                    wrapper.classList.remove('open');

                } else {

                    wrapper.classList.remove('prev', 'next');
                    oldWrapper?.classList.add(direction === 'next' ? 'prev' : 'next');

                }

                oldWrapper && setTimeout(() => oldWrapper.remove(), 5000);

            });
        };

        toggleFullscreen(true);
        this.preloadAdjacent(index);
        this.updateNavButtons();
        this.loadMore();

    }

    preloadAdjacent(index) {

        const prevIndex = index > 0 ? index - 1 : null;
        const nextIndex = index < this.gallery.length - 1 ? index + 1 : null;

        [prevIndex, nextIndex].forEach(i => {
            if (i !== null) {
                const img = new Image();
                img.src = this.gallery[i].href;
            }
        });

    }

    updateNavButtons() {

        this.prevBtn.disabled = (this.currentIndex === 0);
        this.nextBtn.disabled = (this.currentIndex === this.gallery.length - 1);

    }

    loadMore() {

        if (this.currentIndex === this.gallery.length - 1) {
            this.dispatchEvent(new CustomEvent('photo.more', {}));
        }

    }

    next() {

        if (this.currentIndex < this.gallery.length - 1) {
            this.show(this.currentIndex + 1, 'next');
        }

    }

    prev() {

        if (this.currentIndex > 0) {
            this.show(this.currentIndex - 1, 'prev');
        }

    }

    close() {

        this.picoView.classList.remove('show-bg');
        toggleFullscreen(false);

        setTimeout(() => {

            this.picoView.classList.add('d-none');
            this.currentWrapper.remove();
            this.currentWrapper = null;
            this.stopSlideshow();
        
        }, 400);

    }

    enableKeys() {

        this.keysEnabled = true;

    }

    disableKeys() {

        this.keysEnabled = false;

    }

    startSlideshow(interval = 4000) {

        this.stopSlideshow();
        this.slideshowBtn.classList.add('playing');
        document.body.classList.add('slideshow-mode');

        toast('Slideshow started');

        this.slideshowTimer = setInterval(() => {
            (this.currentIndex < this.gallery.length - 1) ? this.next() : this.stopSlideshow();
        }, interval);

    }

    stopSlideshow() {

        if (this.slideshowTimer) {

            clearInterval(this.slideshowTimer);
            this.slideshowBtn.classList.remove('playing');
            document.body.classList.remove('slideshow-mode');
            this.slideshowTimer = null;

            toast('Slideshow stopped');

        }

    }

    toggleSlideshow(interval = 3000) {

        this.slideshowTimer ? this.stopSlideshow() : this.startSlideshow(interval);

    }

}