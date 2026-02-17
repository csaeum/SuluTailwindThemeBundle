// @flow
import React from 'react';

/**
 * Available margin values for selection.
 * These correspond to Tailwind CSS spacing scale values.
 */
const MARGIN_VALUES = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 16, 20, 24, 32];

/**
 * MarginSelector field component for the Sulu admin.
 *
 * Displays a compact grid of buttons representing spacing values (0-32).
 * The selected button is highlighted with the primary color. Returns
 * a Tailwind-compatible margin class (e.g., "mt-5", "mb-10").
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {string|number} props.value - Currently selected margin value
 * @param {Function} props.onChange - Callback when a value is selected
 * @param {Object} props.schemaOptions - Options from the form schema
 */
export default class MarginSelector extends React.Component {
    /**
     * Handle a margin value selection.
     *
     * @param {number} marginValue - The selected spacing value
     */
    handleSelect = (marginValue) => {
        const {onChange, schemaOptions} = this.props;
        if (!onChange) {
            return;
        }

        // Determine the prefix from schema options (e.g., "mt", "mb", "my", "mx")
        const prefix = (schemaOptions && schemaOptions.prefix && schemaOptions.prefix.value) || 'mt';
        onChange(`${prefix}-${marginValue}`);
    };

    /**
     * Extract the numeric value from a Tailwind margin class string.
     *
     * @param {string|number|null} value - The current field value
     * @returns {number|null} The numeric margin value, or null if not parseable
     */
    parseCurrentValue(value) {
        if (value === null || value === undefined) {
            return null;
        }

        if (typeof value === 'number') {
            return value;
        }

        // Extract number from Tailwind class like "mt-5"
        const match = String(value).match(/\d+$/);
        return match ? parseInt(match[0], 10) : null;
    }

    render() {
        const {value} = this.props;
        const currentValue = this.parseCurrentValue(value);

        const containerStyle = {
            display: 'flex',
            flexWrap: 'wrap',
            gap: '4px',
            padding: '4px',
        };

        return (
            <div style={containerStyle}>
                {MARGIN_VALUES.map((marginValue) => {
                    const isSelected = currentValue === marginValue;

                    const buttonStyle = {
                        display: 'inline-flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        width: '36px',
                        height: '32px',
                        border: isSelected ? '2px solid #1a73e8' : '1px solid #d0d0d0',
                        borderRadius: '4px',
                        backgroundColor: isSelected ? '#e8f0fe' : '#fff',
                        color: isSelected ? '#1a73e8' : '#333',
                        fontSize: '12px',
                        fontWeight: isSelected ? 'bold' : 'normal',
                        cursor: 'pointer',
                        transition: 'all 0.15s',
                        outline: 'none',
                        padding: 0,
                    };

                    return (
                        <button
                            key={marginValue}
                            type="button"
                            style={buttonStyle}
                            onClick={() => this.handleSelect(marginValue)}
                            title={`Spacing: ${marginValue}`}
                        >
                            {marginValue}
                        </button>
                    );
                })}
            </div>
        );
    }
}
