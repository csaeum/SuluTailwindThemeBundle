// @flow
import React from 'react';
import {getSuluPrimaryColor, getSuluPrimaryAlpha} from '../../utils/suluColors';

/**
 * Default color for wireframe elements when no variant color is set.
 */
const DEFAULT_COLOR = '#cccccc';

/**
 * VariantPicker field component for the Sulu admin.
 *
 * Displays block variants as colorful wireframe previews. Each wireframe shows
 * colored bars representing different text elements (title, subtitle, paragraph,
 * link, hr) over the block background color. Clicking a wireframe selects the variant.
 *
 * Variant data is injected via the static `themeVariants` property, set by the
 * initializer config hook in index.js from ThemeAdmin::getConfig().
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {*} props.value - Currently selected variant key
 * @param {Function} props.onChange - Callback when a variant is selected
 */
export default class VariantPicker extends React.Component {
    /**
     * Block variants from the active theme, set by the config hook.
     * Array of objects with key, label, blockBg, title, subtitle, paragraph, link, hr, list.
     *
     * @type {Array<Object>}
     */
    static themeVariants = [];

    /**
     * Handle variant selection.
     *
     * @param {string} variantKey - The key of the selected variant
     */
    handleSelect = (variantKey) => {
        const {onChange} = this.props;
        if (onChange) {
            onChange(variantKey);
        }
    };

    /**
     * Render a single wireframe preview for a variant.
     *
     * @param {Object} variant - The variant configuration object
     * @param {boolean} isSelected - Whether this variant is currently selected
     * @returns {React.Element} The wireframe preview element
     */
    renderWireframe(variant, isSelected) {
        const blockBg = variant.blockBg || '#ffffff';
        const titleColor = variant.title || DEFAULT_COLOR;
        const subtitleColor = variant.subtitle || DEFAULT_COLOR;
        const paragraphColor = variant.paragraph || DEFAULT_COLOR;
        const linkColor = variant.link || DEFAULT_COLOR;
        const hrColor = variant.hr || DEFAULT_COLOR;
        const listColor = variant.list || DEFAULT_COLOR;

        const primary = getSuluPrimaryColor();
        const primaryShadow = getSuluPrimaryAlpha(0.3);

        const containerStyle = {
            cursor: 'pointer',
            border: isSelected ? `3px solid ${primary}` : '2px solid #e0e0e0',
            borderRadius: '8px',
            overflow: 'hidden',
            transition: 'border-color 0.2s, box-shadow 0.2s',
            boxShadow: isSelected ? `0 0 0 3px ${primaryShadow}` : 'none',
        };

        const previewStyle = {
            backgroundColor: blockBg,
            padding: '16px',
            minHeight: '120px',
        };

        // Wireframe bars representing text elements
        const barStyle = (color, height, width, marginBottom = '6px') => ({
            backgroundColor: color,
            height: height,
            width: width,
            borderRadius: '2px',
            marginBottom: marginBottom,
        });

        const labelStyle = {
            padding: '8px',
            backgroundColor: '#f5f5f5',
            textAlign: 'center',
            fontSize: '12px',
            fontWeight: isSelected ? 'bold' : 'normal',
            color: '#333',
        };

        // Color swatches
        const swatchContainerStyle = {
            display: 'flex',
            flexWrap: 'wrap',
            justifyContent: 'center',
            padding: '4px 8px 8px',
            backgroundColor: '#f5f5f5',
            gap: '3px',
        };

        const swatchStyle = (color) => ({
            width: '14px',
            height: '14px',
            borderRadius: '50%',
            backgroundColor: color,
            border: '1px solid #ddd',
        });

        return (
            <div
                key={variant.key}
                style={containerStyle}
                onClick={() => this.handleSelect(variant.key)}
                role="button"
                tabIndex={0}
                onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        this.handleSelect(variant.key);
                    }
                }}
            >
                <div style={previewStyle}>
                    {/* Title bar (thick) */}
                    <div style={barStyle(titleColor, '10px', '70%')} />
                    {/* Subtitle bar (medium) */}
                    <div style={barStyle(subtitleColor, '7px', '50%')} />
                    {/* Paragraph bars (thin) */}
                    <div style={barStyle(paragraphColor, '4px', '90%')} />
                    <div style={barStyle(paragraphColor, '4px', '85%')} />
                    <div style={barStyle(paragraphColor, '4px', '75%')} />
                    {/* HR line */}
                    <div style={barStyle(hrColor, '2px', '100%', '8px')} />
                    {/* List items */}
                    <div style={barStyle(listColor, '4px', '60%')} />
                    <div style={barStyle(listColor, '4px', '55%')} />
                    {/* Link bar */}
                    <div style={barStyle(linkColor, '5px', '30%', '0')} />
                </div>
                <div style={labelStyle}>
                    {variant.label || variant.key}
                </div>
                <div style={swatchContainerStyle}>
                    <div style={swatchStyle(titleColor)} title="Title" />
                    <div style={swatchStyle(subtitleColor)} title="Subtitle" />
                    <div style={swatchStyle(paragraphColor)} title="Paragraph" />
                    <div style={swatchStyle(linkColor)} title="Link" />
                    <div style={swatchStyle(listColor)} title="List" />
                    <div style={swatchStyle(hrColor)} title="HR" />
                    <div style={swatchStyle(blockBg)} title="Background" />
                </div>
            </div>
        );
    }

    render() {
        const {value} = this.props;
        const variants = VariantPicker.themeVariants || [];

        if (variants.length === 0) {
            return (
                <div style={{padding: '16px', color: '#999', fontStyle: 'italic'}}>
                    No variants configured. Add variants in Settings &gt; Themes &gt; Variants tab.
                </div>
            );
        }

        return (
            <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))',
                gap: '12px',
                padding: '8px',
            }}>
                {variants.map((variant) =>
                    this.renderWireframe(variant, value === variant.key)
                )}
            </div>
        );
    }
}
