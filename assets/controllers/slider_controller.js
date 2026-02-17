import { Controller } from '@hotwired/stimulus';

/**
 * Slider controller — handles horizontal sliders and carousels.
 *
 * Supports two modes:
 *   - "scroll" (default): Scrollable track with snap points
 *   - "carousel": Single-slide display with fade/show transitions
 *
 * Values:
 *   - autoplay (Boolean): Whether to auto-advance slides
 *   - interval (Number): Milliseconds between auto-advances (default: 5000)
 *   - mode (String): "scroll" or "carousel"
 *
 * Targets:
 *   - track: The scrollable container or slide wrapper
 *   - slide: Individual slide elements (carousel mode)
 *   - dots: Container for dot indicators
 */
export default class extends Controller {
    static targets = ['track', 'slide', 'dots'];
    static values = {
        autoplay: { type: Boolean, default: false },
        interval: { type: Number, default: 5000 },
        mode: { type: String, default: 'scroll' },
    };

    /** @type {number} Current slide index (carousel mode) */
    currentSlide = 0;

    /** @type {number|null} Autoplay interval timer ID */
    autoplayTimer = null;

    /** @type {number} Touch start X position */
    touchStartX = 0;

    connect() {
        if (this.modeValue === 'carousel' && this.autoplayValue) {
            this._startAutoplay();
        }

        // Touch support
        this.element.addEventListener('touchstart', this._onTouchStart.bind(this), { passive: true });
        this.element.addEventListener('touchend', this._onTouchEnd.bind(this), { passive: true });
    }

    disconnect() {
        this._stopAutoplay();
    }

    /** Navigate to the previous slide/position. */
    prev() {
        if (this.modeValue === 'carousel') {
            this.currentSlide = (this.currentSlide - 1 + this.slideTargets.length) % this.slideTargets.length;
            this._showSlide();
        } else if (this.hasTrackTarget) {
            const scrollAmount = this.trackTarget.offsetWidth * 0.8;
            this.trackTarget.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        }
    }

    /** Navigate to the next slide/position. */
    next() {
        if (this.modeValue === 'carousel') {
            this.currentSlide = (this.currentSlide + 1) % this.slideTargets.length;
            this._showSlide();
        } else if (this.hasTrackTarget) {
            const scrollAmount = this.trackTarget.offsetWidth * 0.8;
            this.trackTarget.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }

    /**
     * Jump to a specific slide index.
     *
     * @param {Event} event - Event with params.index
     */
    goTo(event) {
        const index = parseInt(event.params.index, 10);
        if (!isNaN(index) && index >= 0 && index < this.slideTargets.length) {
            this.currentSlide = index;
            this._showSlide();
        }
    }

    /**
     * Show the current slide and hide all others (carousel mode).
     *
     * @private
     */
    _showSlide() {
        this.slideTargets.forEach((slide, idx) => {
            slide.classList.toggle('hidden', idx !== this.currentSlide);
        });

        this._updateDots();
        this._resetAutoplay();
    }

    /**
     * Update dot indicator states.
     *
     * @private
     */
    _updateDots() {
        if (!this.hasDotsTarget) return;

        const dots = this.dotsTarget.querySelectorAll('button');
        dots.forEach((dot, idx) => {
            if (idx === this.currentSlide) {
                dot.classList.remove('bg-white/50');
                dot.classList.add('bg-white');
            } else {
                dot.classList.remove('bg-white');
                dot.classList.add('bg-white/50');
            }
        });
    }

    /**
     * Start autoplay timer.
     *
     * @private
     */
    _startAutoplay() {
        this.autoplayTimer = setInterval(() => {
            this.next();
        }, this.intervalValue);
    }

    /**
     * Stop autoplay timer.
     *
     * @private
     */
    _stopAutoplay() {
        if (this.autoplayTimer) {
            clearInterval(this.autoplayTimer);
            this.autoplayTimer = null;
        }
    }

    /**
     * Reset autoplay timer after manual interaction.
     *
     * @private
     */
    _resetAutoplay() {
        if (this.autoplayValue) {
            this._stopAutoplay();
            this._startAutoplay();
        }
    }

    /**
     * Record touch start position for swipe detection.
     *
     * @param {TouchEvent} event
     * @private
     */
    _onTouchStart(event) {
        this.touchStartX = event.changedTouches[0].clientX;
    }

    /**
     * Detect swipe direction and navigate accordingly.
     *
     * @param {TouchEvent} event
     * @private
     */
    _onTouchEnd(event) {
        const touchEndX = event.changedTouches[0].clientX;
        const diff = this.touchStartX - touchEndX;
        const threshold = 50;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.next();
            } else {
                this.prev();
            }
        }
    }
}
