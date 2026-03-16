// @flow
import React, {Fragment} from 'react';
import {ChromePicker} from 'react-color';
import {translate} from 'sulu-admin-bundle/utils';
import {Requester} from 'sulu-admin-bundle/services';
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
 * Shade levels matching Tailwind CSS v4.
 */
const SHADE_LEVELS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

/**
 * Palette color names and their translation keys.
 */
const PALETTE_COLORS = [
    {key: 'primary', label: 'iw_sulu_tailwind_theme.colors_primary'},
    {key: 'secondary', label: 'iw_sulu_tailwind_theme.colors_secondary'},
    {key: 'accent', label: 'iw_sulu_tailwind_theme.colors_accent'},
    {key: 'background', label: 'iw_sulu_tailwind_theme.colors_background'},
];

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
        .iw-palette-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            background: #fafafa;
            border-radius: 3px 3px 0 0;
        }
        .iw-palette-tab {
            flex: 1;
            padding: 8px 12px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: color 0.15s, border-color 0.15s;
        }
        .iw-palette-tab:hover {
            color: #333;
        }
        .iw-palette-tab--active {
            color: #1a56db;
            border-bottom-color: #1a56db;
        }
        .iw-palette-row {
            padding: 4px 8px;
        }
        .iw-palette-row-label {
            font-size: 11px;
            color: #999;
            margin-bottom: 2px;
            text-transform: capitalize;
        }
        .iw-palette-swatches {
            display: flex;
            gap: 2px;
        }
        .iw-palette-swatch {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.08);
            cursor: pointer;
            transition: transform 0.12s, box-shadow 0.12s;
            position: relative;
        }
        .iw-palette-swatch:hover {
            transform: scale(1.15);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .iw-palette-swatch--selected {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1a56db;
        }
        .iw-palette-swatch--selected:hover {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1a56db, 0 2px 8px rgba(0,0,0,0.2);
        }
        .iw-palette-tooltip {
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.12s;
        }
        .iw-palette-swatch:hover .iw-palette-tooltip {
            opacity: 1;
        }
        .iw-color-picker-tabbed {
            background: #fff;
            border: 1px solid #c0c0c0;
            border-radius: 3px;
        }
        .iw-color-picker-tabbed .chrome-picker {
            width: 100% !important;
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
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
 * When `show_palette` schema option is enabled, displays a tabbed interface:
 * - Palette tab: OKLCH-generated swatches for primary, secondary, accent, background
 * - Custom tab: ChromePicker + EyeDropper
 *
 * @param {Object} props - Sulu form field props
 * @param {string} props.value - Current color value (hex or "transparent")
 * @param {Function} props.onChange - Callback when color changes
 * @param {boolean} props.disabled - Whether the field is disabled
 * @param {Object} props.schemaOptions - Schema params from XML config
 */
export default class ColorTokenEditor extends React.Component {
    /**
     * Theme palette data populated by index.js from ThemeAdmin::getConfig().
     * Structure: { primary: { 50: "#hex", 100: "#hex", ... }, secondary: {...}, ... }
     */
    static themePalette = {};

    constructor(props) {
        super(props);

        const showPalette = !!props.schemaOptions?.show_palette?.value;

        this.state = {
            popoverOpen: false,
            internalValue: props.value || '',
            activeTab: showPalette ? 'palette' : 'custom',
            localPalette: null,
        };

        this.showPalette = showPalette;
        this.anchorRef = null;
    }

    componentDidMount() {
        ensurePickerStyles();
        this._loadPaletteFromForm();
    }

    /**
     * Load palette from the current form data via API if we are inside
     * a theme edit form (detected by the presence of colors_primary field).
     * Falls back to the global themePalette if not in a theme form.
     */
    _loadPaletteFromForm() {
        const {formInspector} = this.props;
        if (!formInspector || !this.showPalette) return;

        // Check if we are editing a theme (has colors_primary in form data)
        const primary = formInspector.getValueByPath('/colors_primary');
        if (!primary) return;

        const secondary = formInspector.getValueByPath('/colors_secondary') || '';
        const accent = formInspector.getValueByPath('/colors_accent') || '';
        const background = formInspector.getValueByPath('/colors_background') || '';

        const params = new URLSearchParams();
        if (primary) params.set('primary', primary);
        if (secondary) params.set('secondary', secondary);
        if (accent) params.set('accent', accent);
        if (background) params.set('background', background);

        Requester.get('/admin/api/iw-theme-palette?' + params.toString())
            .then((palette) => {
                this.setState({localPalette: palette});
            })
            .catch(() => {
                // Fallback to global palette on error
            });
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

    /**
     * Handle palette swatch click.
     */
    handleSwatchClick = (hex) => {
        this.setState({internalValue: hex});
        this.props.onChange(hex);

        if (this.props.onFinish) {
            this.props.onFinish();
        }
    };

    /**
     * Copy the current color to clipboard.
     */
    handleCopy = () => {
        const {internalValue} = this.state;
        if (internalValue && navigator.clipboard) {
            navigator.clipboard.writeText(internalValue);
        }
    };

    /**
     * Switch active tab.
     */
    handleTabChange = (tab) => {
        this.setState({activeTab: tab});
    };

    /**
     * Render palette rows with swatches for each color.
     */
    renderPaletteTab() {
        const palette = this.state.localPalette || ColorTokenEditor.themePalette;
        const {internalValue} = this.state;
        const normalizedValue = (internalValue || '').toLowerCase();

        return (
            <div style={{padding: '6px 0', maxHeight: '280px', overflowY: 'auto'}}>
                {PALETTE_COLORS.map(({key, label}) => {
                    const shades = palette[key];
                    if (!shades || Object.keys(shades).length === 0) {
                        return null;
                    }

                    return (
                        <div key={key} className="iw-palette-row">
                            <div className="iw-palette-row-label">
                                {translate(label)}
                            </div>
                            <div className="iw-palette-swatches">
                                {SHADE_LEVELS.map((shade) => {
                                    const hex = shades[shade];
                                    if (!hex) return null;

                                    const isSelected = normalizedValue === hex.toLowerCase();
                                    const swatchClass = 'iw-palette-swatch'
                                        + (isSelected ? ' iw-palette-swatch--selected' : '');

                                    return (
                                        <div
                                            key={shade}
                                            className={swatchClass}
                                            style={{backgroundColor: hex}}
                                            onClick={() => this.handleSwatchClick(hex)}
                                            title={`${key}-${shade}`}
                                        >
                                            <span className="iw-palette-tooltip">
                                                {key.charAt(0).toUpperCase() + key.slice(1)} {shade}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    );
                })}
            </div>
        );
    }

    /**
     * Render the ChromePicker + EyeDropper custom tab.
     */
    renderCustomTab() {
        const {internalValue} = this.state;
        const isTransparent = internalValue === 'transparent';

        return (
            <div>
                <ChromePicker
                    color={isTransparent ? undefined : (internalValue || undefined)}
                    disableAlpha={false}
                    onChangeComplete={this.handlePickerChange}
                />
                {EYEDROPPER_SUPPORTED && (
                    <div style={{
                        padding: '6px 8px',
                    }}>
                        <button
                            type="button"
                            onClick={this.handleEyeDropper}
                            style={{
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
                                width: '100%',
                            }}
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
                    </div>
                )}
            </div>
        );
    }

    /**
     * Render the footer (transparent button + hex input + copy) shared by both tabs.
     */
    renderFooter() {
        const {internalValue} = this.state;

        const footerBtnStyle = {
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '4px',
            padding: '5px 8px',
            border: '1px solid #c0c0c0',
            borderRadius: '3px',
            backgroundColor: '#fff',
            cursor: 'pointer',
            fontSize: '11px',
            color: '#333',
        };

        return (
            <div style={{
                display: 'flex',
                gap: '6px',
                padding: '8px',
                borderTop: '1px solid #e0e0e0',
                alignItems: 'center',
            }}>
                <button
                    type="button"
                    onClick={this.handleTransparent}
                    style={footerBtnStyle}
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
                </button>
                <input
                    type="text"
                    value={internalValue}
                    onChange={(e) => this.handleInputChange(e.target.value)}
                    onBlur={this.handleBlur}
                    style={{
                        flex: 1,
                        padding: '4px 6px',
                        border: '1px solid #c0c0c0',
                        borderRadius: '3px',
                        fontSize: '11px',
                        fontFamily: 'monospace',
                        minWidth: 0,
                    }}
                    placeholder="#000000"
                />
                <button
                    type="button"
                    onClick={this.handleCopy}
                    style={footerBtnStyle}
                    title="Copy"
                >
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" strokeWidth="2" strokeLinecap="round"
                        strokeLinejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                </button>
            </div>
        );
    }

    render() {
        const {disabled, error, dataPath} = this.props;
        const {popoverOpen, internalValue, activeTab} = this.state;

        const isTransparent = internalValue === 'transparent';
        const displayColor = isTransparent
            ? 'transparent'
            : (HEX_COLOR_PATTERN.test(internalValue) ? internalValue : '#000000');

        const iconStyle = {
            color: isTransparent ? 'transparent' : displayColor,
        };

        const hasPalette = this.showPalette
            && ColorTokenEditor.themePalette
            && Object.keys(ColorTokenEditor.themePalette).length > 0;

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
                            style={{...popoverStyle, width: hasPalette ? '380px' : undefined}}
                        >
                            {hasPalette ? (
                                <div className="iw-color-picker-tabbed">
                                    {/* Tab bar */}
                                    <div className="iw-palette-tabs">
                                        <button
                                            type="button"
                                            className={
                                                'iw-palette-tab'
                                                + (activeTab === 'palette' ? ' iw-palette-tab--active' : '')
                                            }
                                            onClick={() => this.handleTabChange('palette')}
                                        >
                                            {translate('iw_sulu_tailwind_theme.palette_tab')}
                                        </button>
                                        <button
                                            type="button"
                                            className={
                                                'iw-palette-tab'
                                                + (activeTab === 'custom' ? ' iw-palette-tab--active' : '')
                                            }
                                            onClick={() => this.handleTabChange('custom')}
                                        >
                                            {translate('iw_sulu_tailwind_theme.custom_tab')}
                                        </button>
                                    </div>

                                    {/* Tab content */}
                                    {activeTab === 'palette'
                                        ? this.renderPaletteTab()
                                        : this.renderCustomTab()
                                    }

                                    {/* Common footer */}
                                    {this.renderFooter()}
                                </div>
                            ) : (
                                <div>
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
                                                style={{
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
                                                }}
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
                                            style={{
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
                                            }}
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
                        </div>
                    )}
                </Popover>
            </Fragment>
        );
    }
}
