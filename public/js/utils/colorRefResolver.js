// @flow

/**
 * Valid color names that can be used in ref: values.
 */
const VALID_COLORS = ['primary', 'secondary', 'accent', 'background'];

/**
 * Valid shade levels matching Tailwind CSS v4.
 */
const VALID_SHADES = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

/**
 * Check if a value is a color reference string.
 *
 * @param {*} value The value to check
 * @returns {boolean} True if the value is a ref: string
 */
export function isRef(value) {
    return typeof value === 'string' && value.startsWith('ref:');
}

/**
 * Parse a color reference string into its components.
 *
 * @param {*} value The value to parse (e.g. "ref:primary-500")
 * @returns {{colorKey: string, shade: number}|null} Parsed ref or null if invalid
 */
export function parseRef(value) {
    if (!isRef(value)) return null;

    const parts = value.substring(4).split('-');
    if (parts.length !== 2) return null;

    const colorKey = parts[0];
    const shade = parseInt(parts[1], 10);

    if (!VALID_COLORS.includes(colorKey) || !VALID_SHADES.includes(shade)) return null;

    return {colorKey, shade};
}

/**
 * Resolve a color reference to its hex value using the provided palette.
 * Returns the original value unchanged if it is not a valid ref.
 *
 * @param {*} value The value to resolve (e.g. "ref:primary-500" or "#ff0000")
 * @param {Object} palette The palette data { primary: { 50: "#hex", ... }, ... }
 * @returns {string} The resolved hex value, or the original value if not a ref
 */
export function resolveRef(value, palette) {
    const parsed = parseRef(value);
    if (!parsed) return value;

    return palette?.[parsed.colorKey]?.[parsed.shade] || value;
}

/**
 * Resolve all ref: values in a flat key-value object.
 *
 * @param {Object} obj A flat object with string values
 * @param {Object} palette The palette data
 * @returns {Object} A new object with all refs resolved to hex
 */
export function resolveAllRefs(obj, palette) {
    const result = {};
    for (const [key, val] of Object.entries(obj)) {
        result[key] = typeof val === 'string' ? resolveRef(val, palette) : val;
    }
    return result;
}
