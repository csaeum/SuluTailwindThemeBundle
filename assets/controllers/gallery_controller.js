import { Controller } from '@hotwired/stimulus';

/**
 * Gallery controller — provides lightbox functionality for image grids.
 *
 * Targets:
 *   - lightbox: The overlay container element
 *   - lightboxImage: The <img> element inside the lightbox
 *   - counter: Optional element to display "X / Y" count
 *
 * Actions:
 *   - open(event): Opens the lightbox at the given image index
 *   - close(): Closes the lightbox overlay
 *   - prev(): Navigates to the previous image
 *   - next(): Navigates to the next image
 */
export default class extends Controller {
    static targets = ['lightbox', 'lightboxImage', 'counter'];

    /** @type {string[]} Full-size image URLs */
    urls = [];

    /** @type {number} Currently displayed image index */
    currentIndex = 0;

    connect() {
        // Collect all full-size image URLs from anchor hrefs
        const links = this.element.querySelectorAll('a[data-action*="gallery#open"]');
        this.urls = Array.from(links).map((link) => link.getAttribute('href'));

        // Close lightbox on Escape key
        this._onKeyDown = this._handleKeyDown.bind(this);
        document.addEventListener('keydown', this._onKeyDown);
    }

    disconnect() {
        document.removeEventListener('keydown', this._onKeyDown);
    }

    /**
     * Open the lightbox at a specific image index.
     *
     * @param {Event} event - Click event from a gallery thumbnail
     */
    open(event) {
        event.preventDefault();

        const index = parseInt(event.params.index, 10) || 0;
        this.currentIndex = index;

        this._showImage();

        if (this.hasLightboxTarget) {
            this.lightboxTarget.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    /** Close the lightbox overlay. */
    close() {
        if (this.hasLightboxTarget) {
            this.lightboxTarget.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    /** Navigate to the previous image. */
    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.urls.length) % this.urls.length;
        this._showImage();
    }

    /** Navigate to the next image. */
    next() {
        this.currentIndex = (this.currentIndex + 1) % this.urls.length;
        this._showImage();
    }

    /**
     * Update the lightbox image source and counter.
     *
     * @private
     */
    _showImage() {
        if (this.hasLightboxImageTarget && this.urls[this.currentIndex]) {
            this.lightboxImageTarget.src = this.urls[this.currentIndex];
        }

        if (this.hasCounterTarget) {
            this.counterTarget.textContent = `${this.currentIndex + 1} / ${this.urls.length}`;
        }
    }

    /**
     * Handle keyboard navigation within the lightbox.
     *
     * @param {KeyboardEvent} event
     * @private
     */
    _handleKeyDown(event) {
        if (!this.hasLightboxTarget || this.lightboxTarget.classList.contains('hidden')) {
            return;
        }

        switch (event.key) {
            case 'Escape':
                this.close();
                break;
            case 'ArrowLeft':
                this.prev();
                break;
            case 'ArrowRight':
                this.next();
                break;
        }
    }
}
