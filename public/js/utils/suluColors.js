// @flow

/**
 * Dynamically detect the Sulu admin primary color at runtime by probing
 * well-known DOM elements that carry the primary color (toolbar buttons,
 * active navigation items, checkboxes, etc.).
 *
 * Falls back to the default Sulu shakespeare color (#52b6ca) if the DOM
 * probe fails (e.g. component renders before the admin shell is ready).
 *
 * This approach is resilient to future Sulu color changes — no hardcoded
 * hex value is relied upon, except as a last-resort fallback.
 */

const FALLBACK = '#52b6ca';
let cached: ?string = null;

/**
 * CSS selectors for elements known to carry the Sulu primary color,
 * paired with the CSS property that holds it.
 *
 * Ordered by reliability / likelihood of being present in the DOM.
 */
const PROBES: Array<{selector: string, property: string}> = [
    // Active navigation item left border
    {selector: '.su-navigation-item.active', property: 'borderLeftColor'},
    // Primary toolbar button background
    {selector: 'button[class*="primary"]', property: 'backgroundColor'},
    // Sulu header toolbar icon buttons on hover (fallback)
    {selector: '.su-toolbar .su-button--primary', property: 'backgroundColor'},
    // Checked checkbox accent
    {selector: 'input[type="checkbox"]:checked', property: 'accentColor'},
];

/**
 * Convert any CSS color string (rgb, rgba, hex, named) to a 6-char hex string.
 *
 * @param {string} cssColor - The CSS color value to normalize
 * @returns {string|null} Hex color (#rrggbb) or null if unparseable
 */
function normalizeToHex(cssColor: string): ?string {
    if (!cssColor || cssColor === 'transparent' || cssColor === 'rgba(0, 0, 0, 0)') {
        return null;
    }

    // Already hex
    if (cssColor.startsWith('#')) {
        return cssColor;
    }

    // rgb(r, g, b) or rgba(r, g, b, a)
    const match = cssColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (match) {
        const r = parseInt(match[1], 10);
        const g = parseInt(match[2], 10);
        const b = parseInt(match[3], 10);

        // Skip black / white / greys — they are not the primary color
        if ((r === g && g === b) || (r + g + b < 30) || (r + g + b > 720)) {
            return null;
        }

        const hex = '#' + [r, g, b].map((c) => c.toString(16).padStart(2, '0')).join('');
        return hex;
    }

    return null;
}

/**
 * Probe the DOM for the Sulu admin primary color.
 *
 * @returns {string|null} Detected hex color or null
 */
function probeColor(): ?string {
    for (const {selector, property} of PROBES) {
        const el = document.querySelector(selector);

        if (el) {
            const computed = getComputedStyle(el);
            const value = computed.getPropertyValue(property) || computed[property];
            const hex = normalizeToHex(value);

            if (hex) {
                return hex;
            }
        }
    }

    return null;
}

/**
 * Get the Sulu admin primary color.
 *
 * On first call, probes the DOM. If the probe fails (admin shell not yet
 * rendered), schedules a deferred retry so the color is ready for
 * subsequent calls.
 *
 * @returns {string} Hex color string
 */
export function getSuluPrimaryColor(): string {
    if (cached) {
        return cached;
    }

    const detected = probeColor();

    if (detected) {
        cached = detected;
        return detected;
    }

    // DOM may not be ready yet — schedule a deferred probe
    if (typeof requestAnimationFrame !== 'undefined') {
        requestAnimationFrame(() => {
            if (!cached) {
                const retry = probeColor();

                if (retry) {
                    cached = retry;
                }
            }
        });
    }

    return FALLBACK;
}

/**
 * Returns the primary color as an rgba() string with the given alpha.
 *
 * @param {number} alpha - Alpha channel value (0-1)
 * @returns {string} rgba() color string
 */
export function getSuluPrimaryAlpha(alpha: number): string {
    const color = getSuluPrimaryColor();

    // If already rgb/rgba, parse and rebuild
    const rgbMatch = color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
    if (rgbMatch) {
        return `rgba(${rgbMatch[1]}, ${rgbMatch[2]}, ${rgbMatch[3]}, ${alpha})`;
    }

    // If hex, convert to rgba
    const hex = color.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

/**
 * Lighter tint of the primary color for selected backgrounds.
 *
 * @returns {string} rgb() color string (90% white mix)
 */
export function getSuluPrimaryTint(): string {
    const color = getSuluPrimaryColor();
    const hex = color.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);

    // Mix with white at 90% to get a very light tint
    const tr = Math.round(r + (255 - r) * 0.9);
    const tg = Math.round(g + (255 - g) * 0.9);
    const tb = Math.round(b + (255 - b) * 0.9);

    return `rgb(${tr}, ${tg}, ${tb})`;
}
