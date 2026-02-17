import { Controller } from '@hotwired/stimulus';
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';

/**
 * Lightbox controller — initializes GLightbox on all images within a block.
 *
 * Scoped to the controller's element: each block gets its own lightbox gallery.
 * Images must be wrapped in <a class="glightbox" href="full-size-url"> to be included.
 *
 * Caption is read from the data-title attribute on the <a> element.
 * Only shown when the user enables "Show image names" in the admin.
 */
export default class extends Controller {
    /** @type {GLightbox|null} GLightbox instance */
    lightbox = null;

    /** @type {Function[]} Click handler references for cleanup */
    _handlers = [];

    connect() {
        const links = this.element.querySelectorAll('a.glightbox');
        if (links.length === 0) return;

        // Build elements array scoped to this block
        const elements = Array.from(links).map((link) => ({
            href: link.getAttribute('href'),
            title: link.getAttribute('data-title') || '',
            type: 'image',
        }));

        this.lightbox = GLightbox({
            elements,
            touchNavigation: true,
            loop: true,
            closeOnOutsideClick: true,
            openEffect: 'fade',
            closeEffect: 'fade',
        });

        // Bind click events to open lightbox at correct index
        links.forEach((link, index) => {
            const handler = (e) => {
                e.preventDefault();
                this.lightbox.openAt(index);
            };
            link.addEventListener('click', handler);
            this._handlers.push({ link, handler });
        });
    }

    disconnect() {
        // Clean up click handlers
        this._handlers.forEach(({ link, handler }) => {
            link.removeEventListener('click', handler);
        });
        this._handlers = [];

        if (this.lightbox) {
            this.lightbox.destroy();
            this.lightbox = null;
        }
    }
}
