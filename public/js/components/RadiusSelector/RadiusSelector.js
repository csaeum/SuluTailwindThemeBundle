// @flow
import React from 'react';
import {getSuluPrimaryColor, getSuluPrimaryTint} from '../../utils/suluColors';

/**
 * Available Tailwind CSS border-radius values.
 * Each entry maps a Tailwind class suffix to its CSS value for preview.
 */
const RADIUS_OPTIONS = [
    {key: 'none', label: 'none', css: '0'},
    {key: 'xs', label: 'xs', css: '2px'},
    {key: 'sm', label: 'sm', css: '4px'},
    {key: 'md', label: 'md', css: '6px'},
    {key: 'lg', label: 'lg', css: '8px'},
    {key: 'xl', label: 'xl', css: '12px'},
    {key: '2xl', label: '2xl', css: '16px'},
    {key: '3xl', label: '3xl', css: '24px'},
    {key: '4xl', label: '4xl', css: '32px'},
    {key: 'full', label: 'full', css: 'calc(infinity * 1px)'},
];

/**
 * RadiusSelector field component for the Sulu admin.
 *
 * Displays a horizontal list of clickable cards. Each card contains a large
 * rectangle preview showing the actual border-radius applied, so differences
 * between xl, 2xl, 3xl, 4xl and full are clearly visible.
 *
 * The stored value is a Tailwind class (e.g., "rounded-md", "rounded-full").
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {string} props.value - Currently selected radius value
 * @param {Function} props.onChange - Callback when a value is selected
 */
export default class RadiusSelector extends React.Component {
    handleSelect = (key) => {
        const {onChange} = this.props;
        if (!onChange) {
            return;
        }

        onChange(`rounded-${key}`);
    };

    /**
     * Extract the radius key from a Tailwind class string.
     *
     * @param {string|null} value - The current field value (e.g., "rounded-md")
     * @returns {string|null} The radius key (e.g., "md"), or null
     */
    parseCurrentValue(value) {
        if (!value) {
            return null;
        }

        const match = String(value).match(/^rounded-(.+)$/);
        return match ? match[1] : null;
    }

    render() {
        const {value} = this.props;
        const currentKey = this.parseCurrentValue(value);
        const primary = getSuluPrimaryColor();
        const tint = getSuluPrimaryTint();

        const containerStyle = {
            display: 'flex',
            flexWrap: 'wrap',
            gap: '6px',
            padding: '4px',
        };

        return (
            <div style={containerStyle}>
                {RADIUS_OPTIONS.map((option) => {
                    const isSelected = currentKey === option.key;

                    const buttonStyle = {
                        display: 'inline-flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'flex-end',
                        gap: '4px',
                        width: '150px',
                        height: '100px',
                        border: isSelected ? `2px solid ${primary}` : '1px solid #d0d0d0',
                        borderRadius: '6px',
                        backgroundColor: isSelected ? tint : '#fff',
                        color: isSelected ? primary : '#555',
                        fontSize: '11px',
                        fontWeight: isSelected ? 'bold' : 'normal',
                        cursor: 'pointer',
                        transition: 'all 0.15s',
                        outline: 'none',
                        padding: '6px 8px',
                    };

                    const previewStyle = {
                        width: '100%',
                        height: '70px',
                        backgroundColor: isSelected ? primary : '#bbb',
                        borderRadius: option.css,
                        transition: 'all 0.15s',
                        opacity: isSelected ? 0.85 : 0.35,
                    };

                    return (
                        <button
                            key={option.key}
                            type="button"
                            style={buttonStyle}
                            onClick={() => this.handleSelect(option.key)}
                            title={`rounded-${option.key} (${option.css})`}
                        >
                            <span style={previewStyle} />
                            <span>{option.label}</span>
                        </button>
                    );
                })}
            </div>
        );
    }
}
