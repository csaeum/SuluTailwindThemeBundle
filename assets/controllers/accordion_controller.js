import { Controller } from '@hotwired/stimulus';

/**
 * Accordion controller — toggles collapsible panels.
 *
 * Each trigger/panel pair works together:
 *   - Click a trigger to toggle its associated panel
 *   - Optional exclusive mode: only one panel open at a time
 *
 * Values:
 *   - exclusive (Boolean): If true, opening one panel closes others (default: true)
 *
 * Targets:
 *   - trigger: Clickable header elements
 *   - panel: Collapsible content panels (must match trigger order)
 */
export default class extends Controller {
    static targets = ['trigger', 'panel'];
    static values = {
        exclusive: { type: Boolean, default: true },
    };

    connect() {
        // Initialize: collapse all panels except the first if it has 'data-open' attribute
        this.panelTargets.forEach((panel, idx) => {
            const isOpen = panel.dataset.open === 'true';
            if (!isOpen) {
                panel.style.maxHeight = '0px';
                panel.style.overflow = 'hidden';
                panel.style.transition = 'max-height 0.3s ease-out';
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                panel.style.overflow = 'hidden';
                panel.style.transition = 'max-height 0.3s ease-out';
                this._setTriggerState(idx, true);
            }
        });
    }

    /**
     * Toggle a panel's visibility.
     *
     * @param {Event} event - Click event from a trigger element
     */
    toggle(event) {
        const trigger = event.currentTarget;
        const idx = this.triggerTargets.indexOf(trigger);
        if (idx === -1) return;

        const panel = this.panelTargets[idx];
        const isOpen = panel.style.maxHeight !== '0px';

        if (this.exclusiveValue && !isOpen) {
            // Close all other panels first
            this.panelTargets.forEach((p, i) => {
                if (i !== idx) {
                    this._closePanel(i);
                }
            });
        }

        if (isOpen) {
            this._closePanel(idx);
        } else {
            this._openPanel(idx);
        }
    }

    /**
     * Open a panel by index.
     *
     * @param {number} idx - Panel index
     * @private
     */
    _openPanel(idx) {
        const panel = this.panelTargets[idx];
        panel.style.maxHeight = panel.scrollHeight + 'px';
        this._setTriggerState(idx, true);
    }

    /**
     * Close a panel by index.
     *
     * @param {number} idx - Panel index
     * @private
     */
    _closePanel(idx) {
        const panel = this.panelTargets[idx];
        panel.style.maxHeight = '0px';
        this._setTriggerState(idx, false);
    }

    /**
     * Update the trigger's visual state (chevron rotation).
     *
     * @param {number} idx - Trigger index
     * @param {boolean} isOpen - Whether the panel is open
     * @private
     */
    _setTriggerState(idx, isOpen) {
        const trigger = this.triggerTargets[idx];
        const icon = trigger.querySelector('[data-accordion-icon]');
        if (icon) {
            icon.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
            icon.style.transition = 'transform 0.3s ease-out';
        }
        trigger.setAttribute('aria-expanded', isOpen.toString());
    }
}
