import { Controller } from '@hotwired/stimulus';

/**
 * Location overlay controller — collapsible info card over a map.
 *
 * Features:
 *   - Collapse/expand with smooth height animation
 *   - Desktop: body capped to section height with hidden-scrollbar scroll + arrow hint
 *   - Mobile: card grows naturally, no scroll limit
 *
 * Targets:
 *   - card:       The floating card container
 *   - header:     The always-visible header area (title + toggle button)
 *   - body:       The collapsible body (address, description, button)
 *   - chevron:    The toggle icon
 *   - scrollHint: The "more content below" arrow indicator
 */
export default class extends Controller {
    static targets = ['card', 'header', 'body', 'chevron', 'scrollHint'];

    /** @type {boolean} */
    _open = false;

    /** @type {Function|null} */
    _scrollHandler = null;

    connect() {
        this._open = false;
    }

    disconnect() {
        this._removeScrollListener();
    }

    /**
     * Toggle the card body visibility.
     */
    toggle() {
        this._open = !this._open;

        if (!this._open) {
            this._removeScrollListener();
        }

        this._applyState();
    }

    /**
     * Scroll the body down by a chunk when the scroll hint arrow is clicked.
     */
    scrollDown() {
        if (!this.hasBodyTarget) return;
        this.bodyTarget.scrollBy({ top: 120, behavior: 'smooth' });
    }

    /**
     * Compute the max allowed body height on desktop.
     * Uses the map container height minus the header height and some padding.
     *
     * @returns {number} Max height in pixels
     * @private
     */
    _getDesktopMaxHeight() {
        if (!this.hasCardTarget) {
            return 300;
        }

        // The map container is the card's parent .relative > first child (the map wrapper)
        const mapContainer = this.cardTarget.parentElement;
        if (!mapContainer) return 300;

        const mapHeight = mapContainer.offsetHeight;
        const headerHeight = this.hasHeaderTarget ? this.headerTarget.offsetHeight : 50;
        // Leave room for: card top/bottom margins (1.5rem * 2 = 3rem ≈ 48px) + header + scroll hint
        const available = mapHeight - headerHeight - 80;

        return Math.max(available, 120);
    }

    /**
     * Set up scroll listener to show/hide the scroll hint arrow.
     *
     * @private
     */
    _setupScrollHint() {
        if (!this.hasBodyTarget || !this.hasScrollHintTarget) return;

        const body = this.bodyTarget;
        const hint = this.scrollHintTarget;

        this._scrollHandler = () => {
            const atBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 8;
            hint.style.opacity = atBottom ? '0' : '1';
            hint.style.pointerEvents = atBottom ? 'none' : 'auto';
        };

        body.addEventListener('scroll', this._scrollHandler, { passive: true });

        // Show hint only if content overflows
        requestAnimationFrame(() => {
            const hasOverflow = body.scrollHeight > body.clientHeight + 4;
            hint.style.display = hasOverflow ? 'flex' : 'none';
            hint.style.opacity = hasOverflow ? '1' : '0';
            hint.style.pointerEvents = hasOverflow ? 'auto' : 'none';
        });
    }

    /**
     * Remove the scroll listener and hide hint.
     *
     * @private
     */
    _removeScrollListener() {
        if (this._scrollHandler && this.hasBodyTarget) {
            this.bodyTarget.removeEventListener('scroll', this._scrollHandler);
            this._scrollHandler = null;
        }
        if (this.hasScrollHintTarget) {
            this.scrollHintTarget.style.display = 'none';
            this.scrollHintTarget.style.opacity = '0';
        }
    }

    /**
     * Apply the current open/closed state to the DOM.
     *
     * @private
     */
    _applyState() {
        if (!this.hasBodyTarget) return;

        const body = this.bodyTarget;
        const isMobile = window.innerWidth <= 768;

        if (this._open) {
            // Show and measure natural height
            body.style.overflow = 'hidden';
            body.style.display = 'flex';
            body.style.maxHeight = 'none';
            const naturalHeight = body.scrollHeight;

            // Desktop: cap to available space in the map — Mobile: full height
            const targetHeight = isMobile
                ? naturalHeight
                : Math.min(naturalHeight, this._getDesktopMaxHeight());

            // Start collapsed
            body.style.maxHeight = '0px';
            body.style.opacity = '0';

            // Force reflow
            body.offsetHeight; // eslint-disable-line no-unused-expressions

            // Animate to target
            body.style.transition = 'max-height 0.35s ease-out, opacity 0.25s ease-out';
            body.style.maxHeight = targetHeight + 'px';
            body.style.opacity = '1';

            const onOpen = (e) => {
                if (e.propertyName !== 'max-height') return;
                body.removeEventListener('transitionend', onOpen);
                body.style.transition = '';

                if (isMobile) {
                    body.style.maxHeight = 'none';
                    body.style.overflow = 'visible';
                } else {
                    // Enable hidden-scrollbar scroll
                    body.style.overflow = '';
                    this._setupScrollHint();
                }
            };
            body.addEventListener('transitionend', onOpen);
        } else {
            // Collapse — use offsetHeight (visible height), not scrollHeight (full content)
            const height = body.offsetHeight;
            body.style.overflow = 'hidden';
            body.style.maxHeight = height + 'px';
            body.style.transition = 'none';

            // Force reflow
            body.offsetHeight; // eslint-disable-line no-unused-expressions

            body.style.transition = 'max-height 0.3s ease-in, opacity 0.2s ease-in';
            body.style.maxHeight = '0px';
            body.style.opacity = '0';

            const onClose = (e) => {
                if (e.propertyName !== 'max-height') return;
                body.removeEventListener('transitionend', onClose);
                body.style.display = 'none';
                body.style.transition = '';
            };
            body.addEventListener('transitionend', onClose);
        }

        // Rotate chevron
        if (this.hasChevronTarget) {
            this.chevronTarget.style.transition = 'transform 0.3s ease';
            this.chevronTarget.style.transform = this._open ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        // Aria
        if (this.hasHeaderTarget) {
            this.headerTarget.setAttribute('aria-expanded', this._open.toString());
        }
    }
}
