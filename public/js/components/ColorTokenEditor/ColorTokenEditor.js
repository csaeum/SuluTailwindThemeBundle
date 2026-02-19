// @flow
import React, {Fragment} from 'react';
import {ChromePicker} from 'react-color';
import Input from 'sulu-admin-bundle/components/Input';
import Popover from 'sulu-admin-bundle/components/Popover';

/**
 * Regex for validating hex color codes (3, 6 or 8 digit with alpha).
 */
const HEX_COLOR_PATTERN = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/;

/**
 * Check if the EyeDropper API is available in the browser.
 */
const EYEDROPPER_SUPPORTED = typeof window !== 'undefined' && 'EyeDropper' in window;

/**
 * Inline CSS injected once for the ChromePicker overrides inside the popover.
 */
const PICKER_STYLE_ID = 'iw-color-token-editor-styles';

function ensurePickerStyles() {
    if (document.getElementById(PICKER_STYLE_ID)) {
        return;
    }

    const style = document.createElement('style');
    style.id = PICKER_STYLE_ID;
    style.textContent = `
        .iw-color-picker-popover .chrome-picker {
            box-shadow: none !important;
            border: 1px solid #c0c0c0;
            border-radius: 3px !important;
            font-family: inherit;
        }
        .iw-color-picker-popover .chrome-picker input {
            font-size: 12px !important;
        }
        .iw-color-picker-icon {
            line-height: 1em;
            max-height: 1em;
            max-width: 1em;
            overflow: hidden;
            border: 1px solid #c0c0c0;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Custom color picker field for the Sulu admin.
 *
 * Renders identically to the native Sulu color field (Input + colored icon),
 * but opens a richer picker popover with:
 * - ChromePicker (HEX, RGB, HSL)
 * - EyeDropper API button (browser support required)
 * - Transparent value button
 *
 * @param {Object} props - Sulu form field props
 * @param {string} props.value - Current color value (hex or "transparent")
 * @param {Function} props.onChange - Callback when color changes
 * @param {boolean} props.disabled - Whether the field is disabled
 */
export default class ColorTokenEditor extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            popoverOpen: false,
            internalValue: props.value || '',
        };

        this.anchorRef = null;
    }

    componentDidMount() {
        ensurePickerStyles();
    }

    componentDidUpdate(prevProps) {
        if (prevProps.value !== this.props.value && this.props.value !== this.state.internalValue) {
            this.setState({internalValue: this.props.value || ''});
        }
    }

    setAnchorRef = (ref) => {
        this.anchorRef = ref;
    };

    /**
     * Open the color picker popover.
     */
    handleIconClick = () => {
        if (!this.props.disabled) {
            this.setState({popoverOpen: true});
        }
    };

    /**
     * Close the color picker popover.
     */
    handlePopoverClose = () => {
        this.setState({popoverOpen: false});
        if (this.props.onFinish) {
            this.props.onFinish();
        }
    };

    /**
     * Handle text input changes (typing hex manually).
     */
    handleInputChange = (value) => {
        this.setState({internalValue: value || ''});

        if (!value) {
            this.props.onChange(undefined);
            return;
        }

        if (value === 'transparent' || HEX_COLOR_PATTERN.test(value)) {
            this.props.onChange(value);
        }
    };

    /**
     * Validate and commit value on blur.
     */
    handleBlur = () => {
        const {internalValue} = this.state;

        if (internalValue === 'transparent' || internalValue === '') {
            return;
        }

        let normalized = internalValue;
        if (normalized && !normalized.startsWith('#')) {
            normalized = '#' + normalized;
        }

        if (HEX_COLOR_PATTERN.test(normalized)) {
            this.setState({internalValue: normalized});
            this.props.onChange(normalized);
        } else {
            // Reset to last valid value
            this.setState({internalValue: this.props.value || ''});
        }

        if (this.props.onFinish) {
            this.props.onFinish();
        }
    };

    /**
     * Handle ChromePicker color change.
     */
    handlePickerChange = (color) => {
        if (color && color.hex) {
            const hexValue = color.hex;
            this.setState({internalValue: hexValue});
            this.props.onChange(hexValue);

            if (this.props.onFinish) {
                this.props.onFinish();
            }
        }
    };

    /**
     * Use the EyeDropper API to pick a color from the screen.
     */
    handleEyeDropper = async () => {
        if (!EYEDROPPER_SUPPORTED) {
            return;
        }

        try {
            const eyeDropper = new window.EyeDropper();
            const result = await eyeDropper.open();
            if (result && result.sRGBHex) {
                this.setState({internalValue: result.sRGBHex});
                this.props.onChange(result.sRGBHex);

                if (this.props.onFinish) {
                    this.props.onFinish();
                }
            }
        } catch (e) {
            // User cancelled the eyedropper — do nothing
        }
    };

    /**
     * Set the value to "transparent".
     */
    handleTransparent = () => {
        this.setState({internalValue: 'transparent'});
        this.props.onChange('transparent');

        if (this.props.onFinish) {
            this.props.onFinish();
        }

        this.handlePopoverClose();
    };

    render() {
        const {disabled, error, dataPath} = this.props;
        const {popoverOpen, internalValue} = this.state;

        const isTransparent = internalValue === 'transparent';
        const displayColor = isTransparent
            ? 'transparent'
            : (HEX_COLOR_PATTERN.test(internalValue) ? internalValue : '#000000');

        const iconStyle = {
            color: isTransparent ? 'transparent' : displayColor,
        };

        // Toolbar button shared styles
        const toolbarBtnStyle = {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '4px',
            padding: '6px 12px',
            border: '1px solid #c0c0c0',
            borderRadius: '3px',
            backgroundColor: '#fff',
            cursor: 'pointer',
            fontSize: '12px',
            color: '#333',
            flex: 1,
        };

        return (
            <Fragment>
                <Input
                    disabled={!!disabled}
                    icon="su-square"
                    iconClassName="iw-color-picker-icon"
                    iconStyle={iconStyle}
                    id={dataPath}
                    inputContainerRef={this.setAnchorRef}
                    onBlur={this.handleBlur}
                    onChange={this.handleInputChange}
                    onIconClick={!disabled ? this.handleIconClick : undefined}
                    placeholder="#000000"
                    valid={!error}
                    value={internalValue}
                />
                <Popover
                    anchorElement={this.anchorRef}
                    horizontalOffset={35}
                    onClose={this.handlePopoverClose}
                    open={popoverOpen}
                    verticalOffset={-30}
                >
                    {(setPopoverElementRef, popoverStyle) => (
                        <div
                            className="iw-color-picker-popover"
                            ref={setPopoverElementRef}
                            style={popoverStyle}
                        >
                            <ChromePicker
                                color={isTransparent ? undefined : (internalValue || undefined)}
                                disableAlpha={false}
                                onChangeComplete={this.handlePickerChange}
                            />
                            <div style={{
                                display: 'flex',
                                gap: '6px',
                                padding: '8px',
                                backgroundColor: '#fff',
                                border: '1px solid #c0c0c0',
                                borderTop: 'none',
                                borderRadius: '0 0 3px 3px',
                            }}>
                                {EYEDROPPER_SUPPORTED && (
                                    <button
                                        type="button"
                                        onClick={this.handleEyeDropper}
                                        style={toolbarBtnStyle}
                                        title="Pipette"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                                            strokeLinejoin="round">
                                            <path d="M2 22l1-1h3l9-9" />
                                            <path d="M3 21v-3l9-9" />
                                            <path d="M14.5 7.5l-2-2 5.586-5.586a2 2 0 0 1 2.828 0l1.172 1.172a2 2 0 0 1 0 2.828L16.5 9.5l-2-2" />
                                        </svg>
                                        Pipette
                                    </button>
                                )}
                                <button
                                    type="button"
                                    onClick={this.handleTransparent}
                                    style={toolbarBtnStyle}
                                    title="Transparent"
                                >
                                    <span style={{
                                        display: 'inline-block',
                                        width: '14px',
                                        height: '14px',
                                        borderRadius: '2px',
                                        border: '1px solid #c0c0c0',
                                        background: 'repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%) 50% / 8px 8px',
                                    }} />
                                    Transparent
                                </button>
                            </div>
                        </div>
                    )}
                </Popover>
            </Fragment>
        );
    }
}
