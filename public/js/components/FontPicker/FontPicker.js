// @flow
import React from 'react';
import {getSuluPrimaryColor, getSuluPrimaryTint} from '../../utils/suluColors';

/**
 * Cache duration for the font catalog (30 minutes in milliseconds).
 */
const CACHE_DURATION_MS = 30 * 60 * 1000;

/**
 * Maximum number of suggestions to display in the dropdown.
 */
const MAX_SUGGESTIONS = 15;

/**
 * localStorage key for the cached font catalog.
 */
const CACHE_KEY = 'iw_theme_font_catalog';

/**
 * localStorage key for the catalog cache timestamp.
 */
const CACHE_TIME_KEY = 'iw_theme_font_catalog_time';

/**
 * Tab identifiers for the font source selector.
 */
const TAB_GOOGLE = 'google';
const TAB_SYSTEM = 'system';
const TAB_LOCAL = 'local';

/**
 * FontPicker field component for the Sulu admin.
 *
 * Provides a unified font picker with three tabs (Google Fonts, System, Local),
 * autocomplete search, live preview, and sync functionality.
 *
 * Stores its value as a JSON string: {"name": "Inter", "source": "google"}
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {string} props.value - JSON string with font name and source
 * @param {Function} props.onChange - Callback when a font is selected
 * @param {boolean} props.disabled - Whether the field is disabled
 * @param {Object} props.schemaOptions - Schema options (e.g. required)
 */
export default class FontPicker extends React.Component {
    /**
     * Whether a Google Fonts API key is configured (injected from config).
     */
    static hasApiKey = false;

    /**
     * Cached catalog data shared across all FontPicker instances.
     */
    static catalogCache = null;

    constructor(props) {
        super(props);

        const parsed = this.parseValue(props.value);

        this.state = {
            activeTab: parsed.source || TAB_GOOGLE,
            inputValue: parsed.name || '',
            catalog: FontPicker.catalogCache || {google: [], system: [], local: []},
            filteredFonts: [],
            showDropdown: false,
            loading: false,
            syncing: false,
            syncMessage: null,
            previewLoaded: false,
            highlightIndex: -1,
        };

        this.containerRef = React.createRef();
        this.inputRef = React.createRef();
    }

    componentDidMount() {
        this.loadCatalog();
        document.addEventListener('click', this.handleDocumentClick);
    }

    componentWillUnmount() {
        document.removeEventListener('click', this.handleDocumentClick);
    }

    /**
     * Sync input value when the external value changes,
     * and load Google Fonts for dropdown preview when suggestions change.
     *
     * @param {Object} prevProps - Previous component props
     * @param {Object} prevState - Previous component state
     */
    componentDidUpdate(prevProps, prevState) {
        if (prevProps.value !== this.props.value) {
            const parsed = this.parseValue(this.props.value);
            if (parsed.name !== this.state.inputValue) {
                this.setState({
                    inputValue: parsed.name || '',
                    activeTab: parsed.source || this.state.activeTab,
                });
                this.loadFontPreview(parsed.name, parsed.source);
            }
        }

        // Load Google Fonts stylesheets for dropdown items preview
        if (
            this.state.activeTab === TAB_GOOGLE
            && this.state.showDropdown
            && (prevState.filteredFonts !== this.state.filteredFonts || !prevState.showDropdown)
        ) {
            this.loadDropdownFonts(this.state.filteredFonts);
        }
    }

    /**
     * Parse the JSON value string into name and source.
     *
     * @param {string|null} value - The JSON value string
     * @returns {{name: string, source: string}} Parsed font data
     */
    parseValue(value) {
        if (!value || value === '') {
            return {name: '', source: TAB_GOOGLE};
        }

        try {
            const parsed = JSON.parse(value);
            return {
                name: parsed.name || '',
                source: parsed.source || TAB_GOOGLE,
            };
        } catch (e) {
            // Backwards compatibility: plain string = Google font name
            return {name: value, source: TAB_GOOGLE};
        }
    }

    /**
     * Close the dropdown when clicking outside the component.
     *
     * @param {Event} event - The document click event
     */
    handleDocumentClick = (event) => {
        if (this.containerRef.current && !this.containerRef.current.contains(event.target)) {
            this.setState({showDropdown: false, highlightIndex: -1});
        }
    };

    /**
     * Load the font catalog from localStorage cache or fetch from API.
     */
    async loadCatalog() {
        // Use shared static cache if available
        if (FontPicker.catalogCache) {
            this.setState({catalog: FontPicker.catalogCache});
            const parsed = this.parseValue(this.props.value);
            this.loadFontPreview(parsed.name, parsed.source);
            return;
        }

        // Check localStorage cache
        try {
            const cachedTime = localStorage.getItem(CACHE_TIME_KEY);
            const cachedData = localStorage.getItem(CACHE_KEY);

            if (cachedTime && cachedData && (Date.now() - parseInt(cachedTime, 10)) < CACHE_DURATION_MS) {
                const catalog = JSON.parse(cachedData);
                FontPicker.catalogCache = catalog;
                this.setState({catalog});
                const parsed = this.parseValue(this.props.value);
                this.loadFontPreview(parsed.name, parsed.source);
                return;
            }
        } catch (e) {
            // Cache read failed, proceed with fetch
        }

        this.setState({loading: true});

        try {
            const response = await fetch('/admin/api/iw-theme-configs/font-catalog');

            if (response.ok) {
                const data = await response.json();
                const catalog = {
                    google: data.google || [],
                    system: data.system || [],
                    local: data.local || [],
                };

                FontPicker.catalogCache = catalog;

                // Update hasApiKey from the response
                if (typeof data.hasApiKey !== 'undefined') {
                    FontPicker.hasApiKey = data.hasApiKey;
                }

                // Cache in localStorage
                try {
                    localStorage.setItem(CACHE_KEY, JSON.stringify(catalog));
                    localStorage.setItem(CACHE_TIME_KEY, String(Date.now()));
                } catch (e) {
                    // Storage full, ignore
                }

                this.setState({catalog, loading: false});
                const parsed = this.parseValue(this.props.value);
                this.loadFontPreview(parsed.name, parsed.source);
            } else {
                this.setState({loading: false});
            }
        } catch (error) {
            this.setState({loading: false});
        }
    }

    /**
     * Synchronize the font catalog from the Google Fonts API.
     */
    handleSync = async () => {
        this.setState({syncing: true, syncMessage: null});

        try {
            const response = await fetch('/admin/api/iw-theme-configs/font-catalog/sync', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Invalidate caches
                FontPicker.catalogCache = null;
                try {
                    localStorage.removeItem(CACHE_KEY);
                    localStorage.removeItem(CACHE_TIME_KEY);
                } catch (e) {
                    // Ignore
                }

                // Reload catalog
                this.setState({syncing: false, syncMessage: {type: 'success', count: data.count}});
                this.loadCatalog();

                // Clear message after 3 seconds
                setTimeout(() => this.setState({syncMessage: null}), 3000);
            } else {
                this.setState({syncing: false, syncMessage: {type: 'error', text: data.error || 'Unknown error'}});
                setTimeout(() => this.setState({syncMessage: null}), 5000);
            }
        } catch (error) {
            this.setState({syncing: false, syncMessage: {type: 'error', text: 'Network error'}});
            setTimeout(() => this.setState({syncMessage: null}), 5000);
        }
    };

    /**
     * Load a Google Font stylesheet for preview rendering.
     *
     * @param {string|null} fontFamily - The font family name to load
     * @param {string} source - The font source (google, system, local)
     */
    loadFontPreview(fontFamily, source) {
        if (!fontFamily) {
            this.setState({previewLoaded: false});
            return;
        }

        // Only load Google Fonts externally
        if (source === TAB_GOOGLE) {
            const linkId = 'iw-theme-font-preview';
            let link = document.getElementById(linkId);

            if (!link) {
                link = document.createElement('link');
                link.id = linkId;
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }

            const encoded = fontFamily.replace(/ /g, '+');
            link.href = `https://fonts.googleapis.com/css2?family=${encoded}&display=swap`;
        }

        this.setState({previewLoaded: true});
    }

    /**
     * Load Google Fonts stylesheets for the currently visible dropdown items.
     *
     * Uses the Google Fonts CSS API multi-family syntax to load all visible
     * fonts in a single request for efficient preview rendering.
     *
     * @param {Array} fonts - List of font objects currently displayed in the dropdown
     */
    loadDropdownFonts(fonts) {
        if (!fonts || fonts.length === 0) {
            return;
        }

        const families = fonts
            .map((font) => {
                const family = typeof font === 'string' ? font : font.family;
                return family.replace(/ /g, '+');
            });

        const linkId = 'iw-theme-font-dropdown-preview';
        let link = document.getElementById(linkId);

        if (!link) {
            link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }

        const familyParams = families.map((f) => `family=${f}`).join('&');
        link.href = `https://fonts.googleapis.com/css2?${familyParams}&display=swap`;
    }

    /**
     * Switch the active tab.
     *
     * @param {string} tab - The tab to switch to
     */
    handleTabChange = (tab) => {
        this.setState({
            activeTab: tab,
            showDropdown: false,
            filteredFonts: [],
            highlightIndex: -1,
        });

        // If current value source does not match the tab, keep the input
        // for search but do not change the value yet
    };

    /**
     * Get the font list for the currently active tab.
     *
     * @returns {Array} The list of fonts for filtering
     */
    getFontsForTab() {
        const {activeTab, catalog} = this.state;

        switch (activeTab) {
            case TAB_GOOGLE:
                return catalog.google || [];
            case TAB_SYSTEM:
                return catalog.system || [];
            case TAB_LOCAL:
                return catalog.local || [];
            default:
                return [];
        }
    }

    /**
     * Handle text input changes and filter the font list.
     *
     * @param {Event} event - The input change event
     */
    handleInputChange = (event) => {
        const inputValue = event.target.value;
        const fonts = this.getFontsForTab();

        const filtered = inputValue.length > 0
            ? fonts.filter((font) => {
                const family = typeof font === 'string' ? font : font.family;
                return family.toLowerCase().includes(inputValue.toLowerCase());
            }).slice(0, MAX_SUGGESTIONS)
            : fonts.slice(0, MAX_SUGGESTIONS);

        this.setState({
            inputValue,
            filteredFonts: filtered,
            showDropdown: filtered.length > 0,
            highlightIndex: -1,
        });
    };

    /**
     * Handle keyboard navigation in the dropdown.
     *
     * @param {KeyboardEvent} event - The keyboard event
     */
    handleKeyDown = (event) => {
        const {filteredFonts, highlightIndex, showDropdown} = this.state;

        if (!showDropdown) {
            return;
        }

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.setState({
                    highlightIndex: Math.min(highlightIndex + 1, filteredFonts.length - 1),
                });
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.setState({
                    highlightIndex: Math.max(highlightIndex - 1, 0),
                });
                break;
            case 'Enter':
                event.preventDefault();
                if (highlightIndex >= 0 && highlightIndex < filteredFonts.length) {
                    const font = filteredFonts[highlightIndex];
                    const family = typeof font === 'string' ? font : font.family;
                    this.selectFont(family);
                }
                break;
            case 'Escape':
                this.setState({showDropdown: false, highlightIndex: -1});
                break;
        }
    };

    /**
     * Select a font from the dropdown or system list.
     *
     * @param {string} fontFamily - The selected font family name
     */
    selectFont = (fontFamily) => {
        const {activeTab} = this.state;

        this.setState({
            inputValue: fontFamily,
            showDropdown: false,
            highlightIndex: -1,
        });

        this.loadFontPreview(fontFamily, activeTab);

        if (this.props.onChange) {
            const jsonValue = JSON.stringify({name: fontFamily, source: activeTab});
            this.props.onChange(jsonValue);
        }
    };

    /**
     * Handle input focus to show suggestions.
     */
    handleFocus = () => {
        const {inputValue} = this.state;
        const fonts = this.getFontsForTab();

        const filtered = inputValue.length > 0
            ? fonts.filter((font) => {
                const family = typeof font === 'string' ? font : font.family;
                return family.toLowerCase().includes(inputValue.toLowerCase());
            }).slice(0, MAX_SUGGESTIONS)
            : fonts.slice(0, MAX_SUGGESTIONS);

        this.setState({
            filteredFonts: filtered,
            showDropdown: filtered.length > 0,
        });
    };

    /**
     * Handle input blur: if the user typed a custom value (Google tab, no API key),
     * emit the value so it is persisted.
     */
    handleBlur = () => {
        const {inputValue, activeTab} = this.state;
        const parsed = this.parseValue(this.props.value);

        // Only emit on blur if the value actually changed
        if (inputValue && inputValue !== parsed.name) {
            if (this.props.onChange) {
                const jsonValue = JSON.stringify({name: inputValue, source: activeTab});
                this.props.onChange(jsonValue);
            }
            this.loadFontPreview(inputValue, activeTab);
        }
    };

    /**
     * Clear the current selection.
     */
    handleClear = () => {
        this.setState({
            inputValue: '',
            showDropdown: false,
            previewLoaded: false,
        });

        if (this.props.onChange) {
            this.props.onChange('');
        }
    };

    /**
     * Get the font family name for display.
     *
     * @param {Object|string} font - Font object or string
     * @returns {string} The family name
     */
    getFontFamily(font) {
        return typeof font === 'string' ? font : font.family;
    }

    /**
     * Get the font category for display.
     *
     * @param {Object|string} font - Font object or string
     * @returns {string|null} The category
     */
    getFontCategory(font) {
        return typeof font === 'object' && font !== null ? font.category : null;
    }

    render() {
        const {disabled} = this.props;
        const {
            activeTab, inputValue, filteredFonts, showDropdown,
            loading, syncing, syncMessage, previewLoaded, highlightIndex,
            catalog,
        } = this.state;

        const parsed = this.parseValue(this.props.value);
        const hasGoogleFonts = (catalog.google || []).length > 0;

        // Resolve Sulu admin primary color for consistent UI styling
        const suluPrimary = getSuluPrimaryColor();
        const suluPrimaryTint = getSuluPrimaryTint();

        return (
            <div ref={this.containerRef} className="iw-font-picker">
                {/* Tabs + Sync button */}
                <div className="iw-font-picker__header">
                    <div className="iw-font-picker__tabs">
                        <button
                            type="button"
                            className={`iw-font-picker__tab ${activeTab === TAB_GOOGLE ? 'iw-font-picker__tab--active' : ''}`}
                            onClick={() => this.handleTabChange(TAB_GOOGLE)}
                            disabled={disabled}
                        >
                            Google Fonts
                        </button>
                        <button
                            type="button"
                            className={`iw-font-picker__tab ${activeTab === TAB_SYSTEM ? 'iw-font-picker__tab--active' : ''}`}
                            onClick={() => this.handleTabChange(TAB_SYSTEM)}
                            disabled={disabled}
                        >
                            System
                        </button>
                        <button
                            type="button"
                            className={`iw-font-picker__tab ${activeTab === TAB_LOCAL ? 'iw-font-picker__tab--active' : ''}`}
                            onClick={() => this.handleTabChange(TAB_LOCAL)}
                            disabled={disabled}
                        >
                            Local
                        </button>
                    </div>
                    {FontPicker.hasApiKey && activeTab === TAB_GOOGLE && (
                        <button
                            type="button"
                            className="iw-font-picker__sync"
                            onClick={this.handleSync}
                            disabled={disabled || syncing}
                            title="Sync Google Fonts"
                        >
                            {syncing ? '\u23F3' : '\u21BB'}
                        </button>
                    )}
                </div>

                {/* Sync message */}
                {syncMessage && (
                    <div className={`iw-font-picker__message iw-font-picker__message--${syncMessage.type}`}>
                        {syncMessage.type === 'success'
                            ? `${syncMessage.count} fonts synced`
                            : syncMessage.text
                        }
                    </div>
                )}

                {/* Input area */}
                <div className="iw-font-picker__input-wrapper">
                    <input
                        ref={this.inputRef}
                        type="text"
                        value={inputValue}
                        onChange={this.handleInputChange}
                        onKeyDown={this.handleKeyDown}
                        onFocus={this.handleFocus}
                        onBlur={this.handleBlur}
                        disabled={disabled}
                        className="iw-font-picker__input"
                        placeholder={loading ? 'Loading...' : 'Search for a font...'}
                        autoComplete="off"
                    />
                    {inputValue && !disabled && (
                        <button
                            type="button"
                            className="iw-font-picker__clear"
                            onClick={this.handleClear}
                            onMouseDown={(e) => e.preventDefault()}
                            title="Clear"
                        >
                            &times;
                        </button>
                    )}
                </div>

                {/* Dropdown */}
                {showDropdown && (
                    <div className="iw-font-picker__dropdown">
                        {activeTab === TAB_LOCAL && filteredFonts.length === 0 && (
                            <div className="iw-font-picker__empty">
                                No local fonts available yet.
                            </div>
                        )}
                        {filteredFonts.map((font, index) => {
                            const family = this.getFontFamily(font);
                            const category = this.getFontCategory(font);
                            const fallback = category || 'sans-serif';

                            return (
                                <div
                                    key={family}
                                    className={`iw-font-picker__item ${index === highlightIndex ? 'iw-font-picker__item--highlighted' : ''}`}
                                    onMouseDown={() => this.selectFont(family)}
                                    onMouseEnter={() => this.setState({highlightIndex: index})}
                                >
                                    <span
                                        className="iw-font-picker__item-name"
                                        style={{fontFamily: `"${family}", ${fallback}`}}
                                    >
                                        {family}
                                    </span>
                                    {category && (
                                        <span className="iw-font-picker__item-category">{category}</span>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* No API key info for Google tab */}
                {activeTab === TAB_GOOGLE && !FontPicker.hasApiKey && !hasGoogleFonts && (
                    <div className="iw-font-picker__info">
                        API key not configured. Type the font name manually.
                    </div>
                )}

                {/* Preview */}
                {parsed.name && previewLoaded && (
                    <div
                        className="iw-font-picker__preview"
                        style={{fontFamily: `"${parsed.name}", sans-serif`}}
                    >
                        The quick brown fox jumps over the lazy dog
                    </div>
                )}

                <style>{`
                    .iw-font-picker {
                        position: relative;
                        width: 100%;
                    }
                    .iw-font-picker__header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 6px;
                    }
                    .iw-font-picker__tabs {
                        display: flex;
                        gap: 2px;
                    }
                    .iw-font-picker__tab {
                        padding: 4px 12px;
                        border: 1px solid #d0d0d0;
                        background: #f5f5f5;
                        color: #666;
                        font-size: 12px;
                        cursor: pointer;
                        transition: all 0.15s;
                        border-radius: 0;
                    }
                    .iw-font-picker__tab:first-child {
                        border-radius: 4px 0 0 4px;
                    }
                    .iw-font-picker__tab:last-child {
                        border-radius: 0 4px 4px 0;
                    }
                    .iw-font-picker__tab--active {
                        background: #fff;
                        color: #333;
                        border-color: ${suluPrimary};
                        font-weight: 500;
                    }
                    .iw-font-picker__tab:hover:not(:disabled) {
                        background: #e8e8e8;
                    }
                    .iw-font-picker__tab--active:hover:not(:disabled) {
                        background: #fff;
                    }
                    .iw-font-picker__sync {
                        padding: 4px 8px;
                        border: 1px solid #d0d0d0;
                        background: #f5f5f5;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 14px;
                        line-height: 1;
                        transition: all 0.15s;
                    }
                    .iw-font-picker__sync:hover:not(:disabled) {
                        background: #e0e0e0;
                    }
                    .iw-font-picker__sync:disabled {
                        opacity: 0.5;
                        cursor: not-allowed;
                    }
                    .iw-font-picker__message {
                        padding: 4px 8px;
                        margin-bottom: 6px;
                        border-radius: 4px;
                        font-size: 12px;
                    }
                    .iw-font-picker__message--success {
                        background: #e8f5e9;
                        color: #2e7d32;
                    }
                    .iw-font-picker__message--error {
                        background: #ffebee;
                        color: #c62828;
                    }
                    .iw-font-picker__input-wrapper {
                        position: relative;
                    }
                    .iw-font-picker__input {
                        width: 100%;
                        height: 36px;
                        border: 1px solid #d0d0d0;
                        border-radius: 4px;
                        padding: 0 30px 0 10px;
                        font-size: 14px;
                        color: #333;
                        background-color: #fff;
                        outline: none;
                        box-sizing: border-box;
                        transition: border-color 0.15s;
                    }
                    .iw-font-picker__input:focus {
                        border-color: ${suluPrimary};
                    }
                    .iw-font-picker__input:disabled {
                        background-color: #f5f5f5;
                    }
                    .iw-font-picker__clear {
                        position: absolute;
                        right: 6px;
                        top: 50%;
                        transform: translateY(-50%);
                        background: none;
                        border: none;
                        font-size: 18px;
                        color: #999;
                        cursor: pointer;
                        padding: 2px 4px;
                        line-height: 1;
                    }
                    .iw-font-picker__clear:hover {
                        color: #333;
                    }
                    .iw-font-picker__dropdown {
                        position: absolute;
                        left: 0;
                        right: 0;
                        background: #fff;
                        border: 1px solid #d0d0d0;
                        border-radius: 4px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        z-index: 1000;
                        max-height: 250px;
                        overflow-y: auto;
                        margin-top: 2px;
                    }
                    .iw-font-picker__item {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 8px 12px;
                        cursor: pointer;
                        font-size: 13px;
                        transition: background-color 0.1s;
                    }
                    .iw-font-picker__item:hover,
                    .iw-font-picker__item--highlighted {
                        background-color: ${suluPrimaryTint};
                    }
                    .iw-font-picker__item-name {
                        flex: 1;
                        font-size: 15px;
                    }
                    .iw-font-picker__item-category {
                        color: #999;
                        font-size: 11px;
                        margin-left: 8px;
                    }
                    .iw-font-picker__empty {
                        padding: 12px;
                        color: #999;
                        font-size: 13px;
                        text-align: center;
                    }
                    .iw-font-picker__info {
                        margin-top: 6px;
                        padding: 6px 8px;
                        background: #fff3e0;
                        border-radius: 4px;
                        font-size: 12px;
                        color: #e65100;
                    }
                    .iw-font-picker__preview {
                        margin-top: 8px;
                        padding: 12px;
                        background-color: #f9f9f9;
                        border-radius: 4px;
                        border: 1px solid #eee;
                        font-size: 18px;
                        color: #333;
                    }
                `}</style>
            </div>
        );
    }
}
