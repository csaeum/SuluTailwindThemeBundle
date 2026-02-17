// @flow
import React from 'react';

/**
 * Cache duration for the Google Fonts list (30 minutes in milliseconds).
 */
const CACHE_DURATION_MS = 30 * 60 * 1000;

/**
 * Maximum number of suggestions to display in the dropdown.
 */
const MAX_SUGGESTIONS = 15;

/**
 * FontSelector field component for the Sulu admin.
 *
 * Provides an autocomplete text field for selecting Google Fonts.
 * Fetches the font list from the Google Fonts API (with caching) and
 * shows matching suggestions as the user types. Displays a live preview
 * of the selected font.
 *
 * @param {Object} props - Component props from Sulu form field
 * @param {string} props.value - Currently selected font family name
 * @param {Function} props.onChange - Callback when a font is selected
 * @param {boolean} props.disabled - Whether the field is disabled
 */
export default class FontSelector extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            inputValue: props.value || '',
            fonts: [],
            filteredFonts: [],
            showDropdown: false,
            loading: false,
            previewLoaded: false,
            highlightIndex: -1,
        };

        this.dropdownRef = React.createRef();
        this.inputRef = React.createRef();
    }

    componentDidMount() {
        this.loadFonts();
        document.addEventListener('click', this.handleDocumentClick);
    }

    componentWillUnmount() {
        document.removeEventListener('click', this.handleDocumentClick);
    }

    /**
     * Sync input value when the external value changes.
     *
     * @param {Object} prevProps - Previous component props
     */
    componentDidUpdate(prevProps) {
        if (prevProps.value !== this.props.value && this.props.value !== this.state.inputValue) {
            this.setState({inputValue: this.props.value || ''});
            this.loadFontPreview(this.props.value);
        }
    }

    /**
     * Close the dropdown when clicking outside the component.
     *
     * @param {Event} event - The document click event
     */
    handleDocumentClick = (event) => {
        if (this.dropdownRef.current && !this.dropdownRef.current.contains(event.target)) {
            this.setState({showDropdown: false, highlightIndex: -1});
        }
    };

    /**
     * Load the Google Fonts list, using a cached version if available.
     */
    async loadFonts() {
        // Check cache first
        const cacheKey = 'iw_theme_google_fonts_cache';
        const cacheTimeKey = 'iw_theme_google_fonts_cache_time';

        try {
            const cachedTime = localStorage.getItem(cacheTimeKey);
            const cachedData = localStorage.getItem(cacheKey);

            if (cachedTime && cachedData && (Date.now() - parseInt(cachedTime, 10)) < CACHE_DURATION_MS) {
                const fonts = JSON.parse(cachedData);
                this.setState({fonts});
                return;
            }
        } catch (e) {
            // Cache read failed, proceed with fetch
        }

        this.setState({loading: true});

        try {
            // Use the Sulu backend proxy to fetch fonts (avoids CORS and API key exposure)
            const response = await fetch('/admin/api/iw-theme-configs/google-fonts');

            if (response.ok) {
                const data = await response.json();
                const fontNames = (data.items || []).map((item) => item.family);

                // Cache the result
                try {
                    localStorage.setItem(cacheKey, JSON.stringify(fontNames));
                    localStorage.setItem(cacheTimeKey, String(Date.now()));
                } catch (e) {
                    // Storage full, ignore
                }

                this.setState({fonts: fontNames, loading: false});
            } else {
                this.setState({loading: false});
            }
        } catch (error) {
            this.setState({loading: false});
        }
    }

    /**
     * Load a Google Font stylesheet for preview rendering.
     *
     * @param {string|null} fontFamily - The font family name to load
     */
    loadFontPreview(fontFamily) {
        if (!fontFamily) {
            this.setState({previewLoaded: false});
            return;
        }

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
        this.setState({previewLoaded: true});
    }

    /**
     * Handle text input changes and filter the font list.
     *
     * @param {Event} event - The input change event
     */
    handleInputChange = (event) => {
        const inputValue = event.target.value;
        const {fonts} = this.state;

        const filtered = inputValue.length > 0
            ? fonts.filter((font) =>
                font.toLowerCase().includes(inputValue.toLowerCase())
            ).slice(0, MAX_SUGGESTIONS)
            : [];

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
                    this.selectFont(filteredFonts[highlightIndex]);
                }
                break;
            case 'Escape':
                this.setState({showDropdown: false, highlightIndex: -1});
                break;
        }
    };

    /**
     * Select a font from the suggestions dropdown.
     *
     * @param {string} fontFamily - The selected font family name
     */
    selectFont = (fontFamily) => {
        this.setState({
            inputValue: fontFamily,
            showDropdown: false,
            highlightIndex: -1,
        });

        this.loadFontPreview(fontFamily);

        if (this.props.onChange) {
            this.props.onChange(fontFamily);
        }
    };

    /**
     * Handle input focus to show suggestions.
     */
    handleFocus = () => {
        const {inputValue, fonts} = this.state;

        if (inputValue.length > 0) {
            const filtered = fonts.filter((font) =>
                font.toLowerCase().includes(inputValue.toLowerCase())
            ).slice(0, MAX_SUGGESTIONS);

            this.setState({
                filteredFonts: filtered,
                showDropdown: filtered.length > 0,
            });
        }
    };

    render() {
        const {value, disabled} = this.props;
        const {inputValue, filteredFonts, showDropdown, loading, previewLoaded, highlightIndex} = this.state;

        const containerStyle = {
            position: 'relative',
            width: '100%',
        };

        const inputStyle = {
            width: '100%',
            height: '36px',
            border: '1px solid #d0d0d0',
            borderRadius: '4px',
            padding: '0 10px',
            fontSize: '14px',
            color: '#333',
            backgroundColor: disabled ? '#f5f5f5' : '#fff',
            outline: 'none',
            boxSizing: 'border-box',
        };

        const dropdownStyle = {
            position: 'absolute',
            top: '38px',
            left: 0,
            right: 0,
            backgroundColor: '#fff',
            border: '1px solid #d0d0d0',
            borderRadius: '4px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: 1000,
            maxHeight: '250px',
            overflowY: 'auto',
        };

        const itemStyle = (isHighlighted) => ({
            padding: '8px 12px',
            cursor: 'pointer',
            fontSize: '13px',
            backgroundColor: isHighlighted ? '#e8f0fe' : 'transparent',
            transition: 'background-color 0.1s',
        });

        const previewStyle = {
            marginTop: '8px',
            padding: '12px',
            backgroundColor: '#f9f9f9',
            borderRadius: '4px',
            border: '1px solid #eee',
            fontFamily: value ? `"${value}", sans-serif` : 'sans-serif',
            fontSize: '18px',
            color: '#333',
        };

        return (
            <div style={containerStyle} ref={this.dropdownRef}>
                <input
                    ref={this.inputRef}
                    type="text"
                    value={inputValue}
                    onChange={this.handleInputChange}
                    onKeyDown={this.handleKeyDown}
                    onFocus={this.handleFocus}
                    disabled={disabled}
                    style={inputStyle}
                    placeholder={loading ? 'Loading fonts...' : 'Search for a font...'}
                    autoComplete="off"
                />

                {showDropdown && (
                    <div style={dropdownStyle}>
                        {filteredFonts.map((font, index) => (
                            <div
                                key={font}
                                style={itemStyle(index === highlightIndex)}
                                onClick={() => this.selectFont(font)}
                                onMouseEnter={() => this.setState({highlightIndex: index})}
                            >
                                {font}
                            </div>
                        ))}
                    </div>
                )}

                {value && previewLoaded && (
                    <div style={previewStyle}>
                        The quick brown fox jumps over the lazy dog
                    </div>
                )}
            </div>
        );
    }
}
