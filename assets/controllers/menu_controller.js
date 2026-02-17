import { Controller } from '@hotwired/stimulus';

/**
 * Menu controller — handles mobile menu toggle, dropdowns, and scroll behavior.
 *
 * Targets:
 *   - panel: The mobile menu panel (or fullscreen overlay)
 *   - burger: The burger button element
 *   - iconOpen: The "open" hamburger icon
 *   - iconClose: The "close" (X) icon
 *   - dropdown: Desktop dropdown menu containers
 *   - submenu: Mobile submenu containers (toggled independently)
 *
 * Actions:
 *   - toggle(): Toggle mobile menu open/close
 *   - close(): Close the mobile menu
 *   - toggleDropdown(event): Toggle a desktop dropdown
 *   - toggleMobileSubmenu(event): Toggle a mobile submenu accordion
 */
export default class extends Controller {
    static targets = ['panel', 'burger', 'iconOpen', 'iconClose', 'dropdown', 'submenu'];

    /** @type {boolean} Whether the mobile menu is currently open */
    isOpen = false;

    connect() {
        // Close dropdowns when clicking outside
        this._onDocumentClick = this._handleDocumentClick.bind(this);
        document.addEventListener('click', this._onDocumentClick);

        // Scroll behavior: add shadow on scroll
        this._onScroll = this._handleScroll.bind(this);
        window.addEventListener('scroll', this._onScroll, { passive: true });
    }

    disconnect() {
        document.removeEventListener('click', this._onDocumentClick);
        window.removeEventListener('scroll', this._onScroll);
    }

    /** Toggle the mobile menu panel visibility. */
    toggle() {
        this.isOpen = !this.isOpen;
        this._updateState();
    }

    /** Close the mobile menu. */
    close() {
        this.isOpen = false;
        this._updateState();
    }

    /**
     * Toggle a desktop dropdown menu.
     *
     * @param {Event} event
     */
    toggleDropdown(event) {
        const button = event.currentTarget;
        const dropdown = button.nextElementSibling;

        if (!dropdown) return;

        // Close all other dropdowns first
        this.dropdownTargets.forEach((dd) => {
            if (dd !== dropdown) {
                dd.classList.add('hidden');
            }
        });

        dropdown.classList.toggle('hidden');
    }

    /**
     * Toggle a mobile submenu accordion.
     *
     * @param {Event} event
     */
    toggleMobileSubmenu(event) {
        const button = event.currentTarget;
        const submenu = button.nextElementSibling;
        const arrow = button.querySelector('svg');

        if (!submenu) return;

        const isHidden = submenu.classList.contains('hidden');
        submenu.classList.toggle('hidden');

        // Rotate arrow indicator
        if (arrow) {
            arrow.style.transform = isHidden ? 'rotate(180deg)' : '';
        }
    }

    /**
     * Update the visual state of the mobile menu.
     *
     * @private
     */
    _updateState() {
        if (this.hasPanelTarget) {
            this.panelTarget.classList.toggle('hidden', !this.isOpen);
        }

        if (this.hasIconOpenTarget) {
            this.iconOpenTarget.classList.toggle('hidden', this.isOpen);
        }

        if (this.hasIconCloseTarget) {
            this.iconCloseTarget.classList.toggle('hidden', !this.isOpen);
        }

        // Prevent body scroll when menu is open
        document.body.style.overflow = this.isOpen ? 'hidden' : '';
    }

    /**
     * Close desktop dropdowns when clicking outside.
     *
     * @param {Event} event
     * @private
     */
    _handleDocumentClick(event) {
        if (!this.element.contains(event.target)) {
            this.dropdownTargets.forEach((dd) => dd.classList.add('hidden'));
        }
    }

    /**
     * Add shadow to the header when scrolled past threshold.
     *
     * @private
     */
    _handleScroll() {
        const scrolled = window.scrollY > 10;
        this.element.classList.toggle('shadow-md', scrolled);
    }
}
