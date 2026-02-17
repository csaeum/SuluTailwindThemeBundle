// @flow
import React from 'react';

/**
 * Regex pattern for validating hex color codes (3 or 6 digit).
 */
const HEX_COLOR_PATTERN = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/;

/**
 * ColorTokenEditor field component for the Sulu admin.
 *
 * Provides a color picker input alongside a hex text field, with a color
 * preview swatch. Both inputs stay in sync: changing the color picker
 * updates the text field and vice versa.
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {string} props.value - Current hex color value (e.g., "#ff0000")
 * @param {Function} props.onChange - Callback when the color changes
 * @param {boolean} props.disabled - Whether the field is disabled
 */
export default class ColorTokenEditor extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            textValue: props.value || '#000000',
        };
    }

    /**
     * Sync internal text state when the external value changes.
     *
     * @param {Object} prevProps - Previous component props
     */
    componentDidUpdate(prevProps) {
        if (prevProps.value !== this.props.value && this.props.value !== this.state.textValue) {
            this.setState({textValue: this.props.value || '#000000'});
        }
    }

    /**
     * Handle changes from the native color input.
     *
     * @param {Event} event - The input change event
     */
    handleColorInputChange = (event) => {
        const color = event.target.value;
        this.setState({textValue: color});
        if (this.props.onChange) {
            this.props.onChange(color);
        }
    };

    /**
     * Handle changes from the hex text input.
     * Only commits valid hex values to the parent form.
     *
     * @param {Event} event - The input change event
     */
    handleTextInputChange = (event) => {
        const text = event.target.value;
        this.setState({textValue: text});

        // Only propagate valid hex colors
        if (HEX_COLOR_PATTERN.test(text)) {
            if (this.props.onChange) {
                this.props.onChange(text);
            }
        }
    };

    /**
     * Normalize incomplete hex values on blur.
     */
    handleTextInputBlur = () => {
        const {textValue} = this.state;

        // Auto-prepend # if missing
        let normalized = textValue;
        if (normalized && !normalized.startsWith('#')) {
            normalized = '#' + normalized;
        }

        if (HEX_COLOR_PATTERN.test(normalized)) {
            this.setState({textValue: normalized});
            if (this.props.onChange) {
                this.props.onChange(normalized);
            }
        } else {
            // Reset to last valid value
            this.setState({textValue: this.props.value || '#000000'});
        }
    };

    render() {
        const {value, disabled} = this.props;
        const {textValue} = this.state;
        const displayColor = HEX_COLOR_PATTERN.test(value) ? value : '#000000';

        const containerStyle = {
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
        };

        const colorInputStyle = {
            width: '40px',
            height: '36px',
            border: '1px solid #d0d0d0',
            borderRadius: '4px',
            padding: '2px',
            cursor: disabled ? 'not-allowed' : 'pointer',
            backgroundColor: '#fff',
        };

        const textInputStyle = {
            flex: 1,
            height: '36px',
            border: '1px solid #d0d0d0',
            borderRadius: '4px',
            padding: '0 10px',
            fontSize: '14px',
            fontFamily: 'monospace',
            color: '#333',
            backgroundColor: disabled ? '#f5f5f5' : '#fff',
            outline: 'none',
            maxWidth: '120px',
        };

        const swatchStyle = {
            width: '36px',
            height: '36px',
            borderRadius: '4px',
            backgroundColor: displayColor,
            border: '1px solid #d0d0d0',
            flexShrink: 0,
        };

        return (
            <div style={containerStyle}>
                <input
                    type="color"
                    value={displayColor}
                    onChange={this.handleColorInputChange}
                    disabled={disabled}
                    style={colorInputStyle}
                />
                <input
                    type="text"
                    value={textValue}
                    onChange={this.handleTextInputChange}
                    onBlur={this.handleTextInputBlur}
                    disabled={disabled}
                    style={textInputStyle}
                    placeholder="#000000"
                    maxLength={7}
                />
                <div style={swatchStyle} title={displayColor} />
            </div>
        );
    }
}
