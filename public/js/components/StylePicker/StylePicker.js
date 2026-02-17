// @flow
import React from 'react';

/**
 * Layout style configurations for different block types.
 * Each style has a name, label, and SVG wireframe description.
 */
const STYLE_CONFIGS = {
    hero: [
        {key: 'centered', label: 'Centered'},
        {key: 'left-aligned', label: 'Left Aligned'},
        {key: 'split', label: 'Split'},
        {key: 'fullscreen', label: 'Fullscreen'},
    ],
    text_image: [
        {key: 'image-left', label: 'Image Left'},
        {key: 'image-right', label: 'Image Right'},
        {key: 'image-top', label: 'Image Top'},
        {key: 'image-bottom', label: 'Image Bottom'},
    ],
    gallery: [
        {key: 'grid', label: 'Grid'},
        {key: 'masonry', label: 'Masonry'},
        {key: 'carousel', label: 'Carousel'},
        {key: 'lightbox', label: 'Lightbox'},
    ],
    cta: [
        {key: 'banner', label: 'Banner'},
        {key: 'card', label: 'Card'},
        {key: 'inline', label: 'Inline'},
    ],
    default: [
        {key: 'standard', label: 'Standard'},
        {key: 'wide', label: 'Wide'},
        {key: 'narrow', label: 'Narrow'},
    ],
};

/**
 * StylePicker field component for the Sulu admin.
 *
 * Displays layout style options as simplified SVG wireframes showing
 * text and image arrangements. The block_type parameter determines
 * which set of styles to display.
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {string} props.value - Currently selected style key
 * @param {Function} props.onChange - Callback when a style is selected
 * @param {Object} props.schemaOptions - Schema options from the form XML
 */
export default class StylePicker extends React.Component {
    /**
     * Handle style selection.
     *
     * @param {string} styleKey - The key of the selected style
     */
    handleSelect = (styleKey) => {
        const {onChange} = this.props;
        if (onChange) {
            onChange(styleKey);
        }
    };

    /**
     * Render a wireframe SVG for the given style.
     *
     * @param {string} styleKey - The style identifier
     * @returns {React.Element} An SVG wireframe visualization
     */
    renderWireframeSvg(styleKey) {
        const fill = '#d1d5db';
        const accent = '#8b5cf6';

        switch (styleKey) {
            case 'centered':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="30" y="10" width="60" height="6" rx="2" fill={accent} />
                        <rect x="20" y="22" width="80" height="4" rx="2" fill={fill} />
                        <rect x="25" y="30" width="70" height="4" rx="2" fill={fill} />
                        <rect x="40" y="42" width="40" height="8" rx="3" fill={accent} />
                        <rect x="25" y="56" width="70" height="20" rx="3" fill={fill} />
                    </svg>
                );
            case 'left-aligned':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="8" y="10" width="50" height="6" rx="2" fill={accent} />
                        <rect x="8" y="22" width="70" height="4" rx="2" fill={fill} />
                        <rect x="8" y="30" width="60" height="4" rx="2" fill={fill} />
                        <rect x="8" y="42" width="35" height="8" rx="3" fill={accent} />
                        <rect x="70" y="10" width="42" height="40" rx="3" fill={fill} />
                    </svg>
                );
            case 'split':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="5" width="52" height="70" rx="3" fill={fill} />
                        <rect x="63" y="15" width="40" height="6" rx="2" fill={accent} />
                        <rect x="63" y="27" width="50" height="4" rx="2" fill={fill} />
                        <rect x="63" y="35" width="45" height="4" rx="2" fill={fill} />
                        <rect x="63" y="50" width="30" height="8" rx="3" fill={accent} />
                    </svg>
                );
            case 'fullscreen':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="0" y="0" width="120" height="80" rx="3" fill={fill} opacity="0.5" />
                        <rect x="25" y="25" width="70" height="8" rx="2" fill={accent} />
                        <rect x="30" y="40" width="60" height="4" rx="2" fill="#fff" />
                        <rect x="40" y="52" width="40" height="8" rx="3" fill={accent} />
                    </svg>
                );
            case 'image-left':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="10" width="45" height="60" rx="3" fill={fill} />
                        <rect x="58" y="15" width="50" height="5" rx="2" fill={accent} />
                        <rect x="58" y="26" width="55" height="3" rx="2" fill={fill} />
                        <rect x="58" y="33" width="50" height="3" rx="2" fill={fill} />
                        <rect x="58" y="40" width="45" height="3" rx="2" fill={fill} />
                    </svg>
                );
            case 'image-right':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="70" y="10" width="45" height="60" rx="3" fill={fill} />
                        <rect x="8" y="15" width="50" height="5" rx="2" fill={accent} />
                        <rect x="8" y="26" width="55" height="3" rx="2" fill={fill} />
                        <rect x="8" y="33" width="50" height="3" rx="2" fill={fill} />
                        <rect x="8" y="40" width="45" height="3" rx="2" fill={fill} />
                    </svg>
                );
            case 'image-top':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="10" y="5" width="100" height="35" rx="3" fill={fill} />
                        <rect x="10" y="46" width="60" height="5" rx="2" fill={accent} />
                        <rect x="10" y="55" width="90" height="3" rx="2" fill={fill} />
                        <rect x="10" y="62" width="80" height="3" rx="2" fill={fill} />
                    </svg>
                );
            case 'image-bottom':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="10" y="5" width="60" height="5" rx="2" fill={accent} />
                        <rect x="10" y="14" width="90" height="3" rx="2" fill={fill} />
                        <rect x="10" y="21" width="80" height="3" rx="2" fill={fill} />
                        <rect x="10" y="32" width="100" height="43" rx="3" fill={fill} />
                    </svg>
                );
            case 'grid':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="5" width="34" height="34" rx="3" fill={fill} />
                        <rect x="43" y="5" width="34" height="34" rx="3" fill={fill} />
                        <rect x="81" y="5" width="34" height="34" rx="3" fill={fill} />
                        <rect x="5" y="43" width="34" height="34" rx="3" fill={fill} />
                        <rect x="43" y="43" width="34" height="34" rx="3" fill={fill} />
                        <rect x="81" y="43" width="34" height="34" rx="3" fill={fill} />
                    </svg>
                );
            case 'masonry':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="5" width="34" height="45" rx="3" fill={fill} />
                        <rect x="43" y="5" width="34" height="30" rx="3" fill={fill} />
                        <rect x="81" y="5" width="34" height="50" rx="3" fill={fill} />
                        <rect x="5" y="54" width="34" height="22" rx="3" fill={fill} />
                        <rect x="43" y="39" width="34" height="37" rx="3" fill={fill} />
                        <rect x="81" y="59" width="34" height="17" rx="3" fill={fill} />
                    </svg>
                );
            case 'carousel':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="15" y="10" width="90" height="55" rx="3" fill={fill} />
                        <polygon points="8,38 15,32 15,44" fill={accent} />
                        <polygon points="112,38 105,32 105,44" fill={accent} />
                        <circle cx="52" cy="72" r="3" fill={accent} />
                        <circle cx="60" cy="72" r="3" fill={fill} />
                        <circle cx="68" cy="72" r="3" fill={fill} />
                    </svg>
                );
            case 'lightbox':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="5" width="25" height="25" rx="2" fill={fill} />
                        <rect x="34" y="5" width="25" height="25" rx="2" fill={fill} />
                        <rect x="63" y="5" width="25" height="25" rx="2" fill={accent} opacity="0.7" />
                        <rect x="92" y="5" width="25" height="25" rx="2" fill={fill} />
                        <rect x="20" y="35" width="80" height="40" rx="3" fill={accent} opacity="0.3" />
                        <rect x="25" y="38" width="70" height="34" rx="2" fill={fill} />
                    </svg>
                );
            case 'banner':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="15" width="110" height="50" rx="4" fill={accent} opacity="0.2" />
                        <rect x="15" y="25" width="60" height="6" rx="2" fill={accent} />
                        <rect x="15" y="37" width="50" height="3" rx="2" fill={fill} />
                        <rect x="80" y="30" width="28" height="10" rx="4" fill={accent} />
                    </svg>
                );
            case 'card':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="20" y="5" width="80" height="70" rx="5" fill="#fff" stroke={fill} strokeWidth="1" />
                        <rect x="30" y="15" width="60" height="6" rx="2" fill={accent} />
                        <rect x="30" y="27" width="55" height="3" rx="2" fill={fill} />
                        <rect x="30" y="34" width="50" height="3" rx="2" fill={fill} />
                        <rect x="35" y="48" width="50" height="10" rx="4" fill={accent} />
                    </svg>
                );
            case 'inline':
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="5" y="30" width="50" height="5" rx="2" fill={accent} />
                        <rect x="5" y="40" width="60" height="3" rx="2" fill={fill} />
                        <rect x="80" y="32" width="35" height="10" rx="4" fill={accent} />
                    </svg>
                );
            default:
                return (
                    <svg viewBox="0 0 120 80" width="120" height="80">
                        <rect x="10" y="10" width="100" height="60" rx="4" fill={fill} />
                        <rect x="20" y="20" width="60" height="5" rx="2" fill={accent} />
                        <rect x="20" y="30" width="80" height="3" rx="2" fill={fill} />
                        <rect x="20" y="38" width="70" height="3" rx="2" fill={fill} />
                    </svg>
                );
        }
    }

    render() {
        const {value, onChange, schemaOptions} = this.props;
        const blockType = (schemaOptions && schemaOptions.block_type && schemaOptions.block_type.value) || 'default';
        const styles = STYLE_CONFIGS[blockType] || STYLE_CONFIGS.default;

        return (
            <div style={{display: 'flex', flexWrap: 'wrap', gap: '12px', padding: '8px'}}>
                {styles.map((style) => {
                    const isSelected = value === style.key;
                    return (
                        <div
                            key={style.key}
                            onClick={() => onChange && onChange(style.key)}
                            role="button"
                            tabIndex={0}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    onChange && onChange(style.key);
                                }
                            }}
                            style={{
                                cursor: 'pointer',
                                border: isSelected ? '3px solid #8b5cf6' : '2px solid #e0e0e0',
                                borderRadius: '8px',
                                padding: '8px',
                                backgroundColor: isSelected ? '#f5f3ff' : '#fafafa',
                                transition: 'all 0.2s',
                                textAlign: 'center',
                                boxShadow: isSelected ? '0 0 0 3px rgba(139, 92, 246, 0.3)' : 'none',
                            }}
                        >
                            {this.renderWireframeSvg(style.key)}
                            <div style={{
                                marginTop: '6px',
                                fontSize: '11px',
                                fontWeight: isSelected ? 'bold' : 'normal',
                                color: isSelected ? '#8b5cf6' : '#666',
                            }}>
                                {style.label}
                            </div>
                        </div>
                    );
                })}
            </div>
        );
    }
}
