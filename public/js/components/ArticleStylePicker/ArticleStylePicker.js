// @flow
import React from 'react';
import {translate} from 'sulu-admin-bundle/utils';
import {getSuluPrimaryColor, getSuluPrimaryAlpha, getSuluPrimaryTint} from '../../utils/suluColors';

/**
 * SVG wireframe renderers for article layout styles.
 * Each function receives fill (primary color) and accent (tint).
 * viewBox is 120×80 for all wireframes.
 */
const WIREFRAME_RENDERERS = {
    // ── News styles ──────────────────────────────────────────────
    news_classic: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="3" width="110" height="28" rx="2" fill={fill} opacity="0.8"/>
            <rect x="5" y="35" width="70" height="5" rx="1" fill={fill}/>
            <rect x="5" y="43" width="50" height="3" rx="1" fill={accent}/>
            <line x1="5" y1="50" x2="115" y2="50" stroke={accent} strokeWidth="0.5"/>
            <rect x="5" y="54" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="60" width="100" height="3" rx="1" fill={accent}/>
            <rect x="5" y="66" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="72" width="80" height="3" rx="1" fill={accent}/>
        </svg>
    ),
    news_magazine: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="3" width="44" height="40" rx="2" fill={fill} opacity="0.8"/>
            <rect x="55" y="5" width="60" height="5" rx="1" fill={fill}/>
            <rect x="55" y="14" width="50" height="4" rx="1" fill={fill} opacity="0.7"/>
            <rect x="55" y="22" width="60" height="3" rx="1" fill={accent}/>
            <rect x="55" y="28" width="55" height="3" rx="1" fill={accent}/>
            <rect x="55" y="34" width="40" height="3" rx="1" fill={accent}/>
            <line x1="5" y1="48" x2="115" y2="48" stroke={accent} strokeWidth="0.5"/>
            <rect x="5" y="52" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="58" width="100" height="3" rx="1" fill={accent}/>
            <rect x="5" y="64" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="70" width="80" height="3" rx="1" fill={accent}/>
        </svg>
    ),
    news_minimal: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="15" y="5" width="90" height="7" rx="1" fill={fill}/>
            <rect x="25" y="16" width="70" height="4" rx="1" fill={fill} opacity="0.6"/>
            <rect x="30" y="24" width="60" height="3" rx="1" fill={accent}/>
            <line x1="25" y1="32" x2="95" y2="32" stroke={accent} strokeWidth="0.5"/>
            <rect x="10" y="38" width="100" height="3" rx="1" fill={accent}/>
            <rect x="10" y="44" width="95" height="3" rx="1" fill={accent}/>
            <rect x="10" y="50" width="100" height="3" rx="1" fill={accent}/>
            <rect x="10" y="56" width="90" height="3" rx="1" fill={accent}/>
            <rect x="10" y="62" width="100" height="3" rx="1" fill={accent}/>
            <rect x="10" y="68" width="70" height="3" rx="1" fill={accent}/>
        </svg>
    ),

    // ── Event styles ─────────────────────────────────────────────
    event_card_info: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="3" width="110" height="35" rx="2" fill={fill} opacity="0.8"/>
            <rect x="65" y="25" width="48" height="25" rx="3" fill="white" stroke={fill} strokeWidth="1"/>
            <rect x="70" y="30" width="30" height="3" rx="1" fill={fill} opacity="0.7"/>
            <rect x="70" y="36" width="35" height="3" rx="1" fill={accent}/>
            <rect x="70" y="42" width="25" height="3" rx="1" fill={accent}/>
            <rect x="5" y="55" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="61" width="100" height="3" rx="1" fill={accent}/>
            <rect x="5" y="67" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="73" width="80" height="3" rx="1" fill={accent}/>
        </svg>
    ),
    event_timeline: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="5" width="28" height="15" rx="2" fill={fill} opacity="0.3"/>
            <rect x="8" y="9" width="20" height="3" rx="1" fill={fill}/>
            <rect x="8" y="14" width="15" height="2" rx="1" fill={accent}/>
            <rect x="5" y="24" width="28" height="15" rx="2" fill={fill} opacity="0.3"/>
            <rect x="8" y="28" width="18" height="3" rx="1" fill={fill}/>
            <rect x="8" y="33" width="22" height="2" rx="1" fill={accent}/>
            <rect x="5" y="43" width="28" height="15" rx="2" fill={fill} opacity="0.3"/>
            <rect x="8" y="47" width="16" height="3" rx="1" fill={fill}/>
            <rect x="8" y="52" width="20" height="2" rx="1" fill={accent}/>
            <line x1="38" y1="5" x2="38" y2="75" stroke={accent} strokeWidth="0.5"/>
            <rect x="42" y="5" width="73" height="3" rx="1" fill={accent}/>
            <rect x="42" y="11" width="73" height="3" rx="1" fill={accent}/>
            <rect x="42" y="17" width="65" height="3" rx="1" fill={accent}/>
            <rect x="42" y="23" width="73" height="3" rx="1" fill={accent}/>
            <rect x="42" y="29" width="70" height="3" rx="1" fill={accent}/>
            <rect x="42" y="35" width="73" height="3" rx="1" fill={accent}/>
            <rect x="42" y="41" width="60" height="3" rx="1" fill={accent}/>
        </svg>
    ),

    // ── Blog styles ──────────────────────────────────────────────
    blog_classic: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="3" width="110" height="22" rx="2" fill={fill} opacity="0.8"/>
            <circle cx="18" cy="32" r="5" fill={fill} opacity="0.5"/>
            <rect x="27" y="30" width="40" height="3" rx="1" fill={fill}/>
            <rect x="27" y="35" width="30" height="2" rx="1" fill={accent}/>
            <rect x="5" y="42" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="48" width="100" height="3" rx="1" fill={accent}/>
            <rect x="5" y="54" width="110" height="3" rx="1" fill={accent}/>
            <line x1="5" y1="62" x2="115" y2="62" stroke={accent} strokeWidth="0.5"/>
            <rect x="5" y="66" width="34" height="10" rx="2" fill={fill} opacity="0.2"/>
            <rect x="43" y="66" width="34" height="10" rx="2" fill={fill} opacity="0.2"/>
            <rect x="81" y="66" width="34" height="10" rx="2" fill={fill} opacity="0.2"/>
        </svg>
    ),
    blog_editorial: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="0" y="0" width="120" height="40" fill={fill} opacity="0.15"/>
            <rect x="20" y="10" width="80" height="7" rx="1" fill={fill}/>
            <rect x="30" y="20" width="60" height="4" rx="1" fill={fill} opacity="0.6"/>
            <rect x="35" y="27" width="50" height="3" rx="1" fill={accent}/>
            <rect x="5" y="44" width="8" height="10" rx="1" fill={fill} opacity="0.5"/>
            <rect x="16" y="44" width="99" height="3" rx="1" fill={accent}/>
            <rect x="16" y="49" width="99" height="3" rx="1" fill={accent}/>
            <rect x="5" y="56" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="62" width="100" height="3" rx="1" fill={accent}/>
            <rect x="5" y="68" width="110" height="3" rx="1" fill={accent}/>
            <rect x="5" y="74" width="80" height="3" rx="1" fill={accent}/>
        </svg>
    ),
    blog_sidebar: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="5" width="80" height="3" rx="1" fill={accent}/>
            <rect x="5" y="11" width="80" height="3" rx="1" fill={accent}/>
            <rect x="5" y="17" width="75" height="3" rx="1" fill={accent}/>
            <rect x="5" y="23" width="80" height="3" rx="1" fill={accent}/>
            <rect x="5" y="29" width="70" height="3" rx="1" fill={accent}/>
            <rect x="5" y="35" width="80" height="3" rx="1" fill={accent}/>
            <rect x="5" y="41" width="80" height="3" rx="1" fill={accent}/>
            <rect x="5" y="47" width="65" height="3" rx="1" fill={accent}/>
            <line x1="90" y1="3" x2="90" y2="75" stroke={accent} strokeWidth="0.5"/>
            <rect x="93" y="5" width="22" height="18" rx="2" fill={fill} opacity="0.2"/>
            <rect x="93" y="27" width="22" height="10" rx="2" fill={fill} opacity="0.15"/>
            <rect x="93" y="41" width="22" height="10" rx="2" fill={fill} opacity="0.15"/>
            <rect x="93" y="55" width="22" height="10" rx="2" fill={fill} opacity="0.15"/>
        </svg>
    ),

    // ── Listing styles ───────────────────────────────────────────
    listing_grid: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="3" width="34" height="20" rx="2" fill={fill} opacity="0.6"/>
            <rect x="5" y="25" width="30" height="3" rx="1" fill={fill}/>
            <rect x="5" y="30" width="25" height="2" rx="1" fill={accent}/>
            <rect x="43" y="3" width="34" height="20" rx="2" fill={fill} opacity="0.6"/>
            <rect x="43" y="25" width="30" height="3" rx="1" fill={fill}/>
            <rect x="43" y="30" width="25" height="2" rx="1" fill={accent}/>
            <rect x="81" y="3" width="34" height="20" rx="2" fill={fill} opacity="0.6"/>
            <rect x="81" y="25" width="30" height="3" rx="1" fill={fill}/>
            <rect x="81" y="30" width="25" height="2" rx="1" fill={accent}/>
            <rect x="5" y="38" width="34" height="20" rx="2" fill={fill} opacity="0.6"/>
            <rect x="5" y="60" width="30" height="3" rx="1" fill={fill}/>
            <rect x="5" y="65" width="25" height="2" rx="1" fill={accent}/>
            <rect x="43" y="38" width="34" height="20" rx="2" fill={fill} opacity="0.6"/>
            <rect x="43" y="60" width="30" height="3" rx="1" fill={fill}/>
            <rect x="43" y="65" width="25" height="2" rx="1" fill={accent}/>
            <rect x="81" y="38" width="34" height="20" rx="2" fill={fill} opacity="0.6"/>
            <rect x="81" y="60" width="30" height="3" rx="1" fill={fill}/>
            <rect x="81" y="65" width="25" height="2" rx="1" fill={accent}/>
        </svg>
    ),
    listing_list: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="5" width="20" height="16" rx="2" fill={fill} opacity="0.6"/>
            <rect x="30" y="7" width="60" height="4" rx="1" fill={fill}/>
            <rect x="30" y="14" width="80" height="3" rx="1" fill={accent}/>
            <line x1="5" y1="25" x2="115" y2="25" stroke={accent} strokeWidth="0.3"/>
            <rect x="5" y="29" width="20" height="16" rx="2" fill={fill} opacity="0.6"/>
            <rect x="30" y="31" width="55" height="4" rx="1" fill={fill}/>
            <rect x="30" y="38" width="75" height="3" rx="1" fill={accent}/>
            <line x1="5" y1="49" x2="115" y2="49" stroke={accent} strokeWidth="0.3"/>
            <rect x="5" y="53" width="20" height="16" rx="2" fill={fill} opacity="0.6"/>
            <rect x="30" y="55" width="65" height="4" rx="1" fill={fill}/>
            <rect x="30" y="62" width="70" height="3" rx="1" fill={accent}/>
        </svg>
    ),
    listing_cards: (fill, accent) => (
        <svg viewBox="0 0 120 80" width="120" height="80">
            <rect x="5" y="3" width="53" height="28" rx="2" fill={fill} opacity="0.6"/>
            <rect x="5" y="33" width="45" height="4" rx="1" fill={fill}/>
            <rect x="5" y="39" width="50" height="3" rx="1" fill={accent}/>
            <rect x="5" y="44" width="40" height="3" rx="1" fill={accent}/>
            <rect x="62" y="3" width="53" height="28" rx="2" fill={fill} opacity="0.6"/>
            <rect x="62" y="33" width="45" height="4" rx="1" fill={fill}/>
            <rect x="62" y="39" width="50" height="3" rx="1" fill={accent}/>
            <rect x="62" y="44" width="40" height="3" rx="1" fill={accent}/>
        </svg>
    ),
};

/**
 * Article style picker — displays visual wireframes for each article layout.
 * Reads the article_type from schemaOptions (XML <param>) to determine which styles to show.
 */
export default class ArticleStylePicker extends React.Component {
    /**
     * Article style options from admin config (set by initializer).
     * Structure: {articleType: [{key: string, label: string}]}
     */
    static articleStyles = {};

    componentDidMount() {
        const {value, onChange} = this.props;
        if ((value === null || value === undefined || value === '') && onChange) {
            const articleType = this.getArticleType();
            const styles = ArticleStylePicker.articleStyles[articleType] || [];
            if (styles.length > 0) {
                setTimeout(() => onChange(styles[0].key), 0);
            }
        }
    }

    /**
     * Read the article_type from XML schema options (<param name="article_type" value="news"/>).
     */
    getArticleType() {
        const {schemaOptions} = this.props;
        if (schemaOptions && schemaOptions.article_type && schemaOptions.article_type.value) {
            return schemaOptions.article_type.value;
        }
        return 'news';
    }

    renderWireframeSvg(styleKey) {
        const fill = getSuluPrimaryColor();
        const accent = getSuluPrimaryAlpha(0.3);
        const articleType = this.getArticleType();
        const fullKey = articleType + '_' + styleKey;

        if (WIREFRAME_RENDERERS[fullKey]) {
            return WIREFRAME_RENDERERS[fullKey](fill, accent);
        }

        return null;
    }

    render() {
        const {value, onChange} = this.props;
        const articleType = this.getArticleType();
        const styles = ArticleStylePicker.articleStyles[articleType] || [];

        if (styles.length === 0) {
            return (
                <div style={{padding: '16px', color: '#999', fontStyle: 'italic'}}>
                    {translate('iw_sulu_tailwind_theme.articles_no_styles')}
                </div>
            );
        }

        const primary = getSuluPrimaryColor();
        const primaryShadow = getSuluPrimaryAlpha(0.3);
        const primaryTint = getSuluPrimaryTint();

        return (
            <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(140px, 1fr))',
                gap: '12px',
                padding: '8px',
            }}>
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
                                border: isSelected ? `3px solid ${primary}` : '2px solid #e0e0e0',
                                borderRadius: '8px',
                                padding: '8px',
                                backgroundColor: isSelected ? primaryTint : '#fafafa',
                                transition: 'all 0.2s',
                                textAlign: 'center',
                                boxShadow: isSelected ? `0 0 0 3px ${primaryShadow}` : 'none',
                            }}
                        >
                            <div style={{width: '100%', display: 'flex', justifyContent: 'center'}}>
                                {this.renderWireframeSvg(style.key)}
                            </div>
                            <div style={{
                                marginTop: '6px',
                                fontSize: '11px',
                                fontWeight: isSelected ? 'bold' : 'normal',
                                color: isSelected ? primary : '#666',
                            }}>
                                {translate(style.label)}
                            </div>
                        </div>
                    );
                })}
            </div>
        );
    }
}
