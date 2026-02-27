# Twig Reference

The SuluThemeBundle provides Twig functions and a global variable to access theme data in your templates.

## Twig functions

### `iw_sulu_theme_css_path()`

Returns the web-accessible path to the compiled CSS file for the active theme.

```twig
{% set themeCssPath = iw_sulu_theme_css_path() %}
{% if themeCssPath is not empty %}
    <link rel="stylesheet" href="{{ themeCssPath }}">
{% endif %}
```

**Returns:** `string` — e.g. `/iw-theme/css/theme-1-abc123ef.css`, or empty string if no active theme.

---

### `iw_sulu_theme_fonts_link()`

Returns HTML `<link>` tags for Google Fonts preconnect and stylesheet.

```twig
{{ iw_sulu_theme_fonts_link()|raw }}
```

**Returns:** `string` (HTML safe) — e.g.:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
```

> The function is marked `is_safe: ['html']`, so `|raw` is optional but recommended for clarity.

---

### `iw_sulu_theme_tokens()`

Returns the complete design tokens array for the active theme.

```twig
{% set tokens = iw_sulu_theme_tokens() %}
{{ tokens.colors.primary }}         {# → #1a73e8 #}
{{ tokens.typography.baseFontSize }} {# → 16px #}
```

**Returns:** `array` — Full token structure (see [Token structure](#token-structure) below).

---

### `iw_sulu_theme_menu_config()`

Returns the menu configuration for the active theme.

```twig
{% set menu = iw_sulu_theme_menu_config() %}
{% if menu is not empty and menu.type is defined %}
    Menu type: {{ menu.type }}
    Animation: {{ menu.animation }}
{% endif %}
```

**Returns:** `array` with keys:

| Key | Type | Description |
|-----|------|-------------|
| `type` | `string` | `navbar`, `burger`, `fullscreen`, or `sidebar` |
| `animation` | `string` | `none`, `slide`, or `fade` |
| `clickParentPage` | `bool` | Whether clicking a parent item navigates to its page |
| `childLevels` | `int` | Number of sub-menu levels to display (1, 2, or 3) |
| `displayLogoDesktop` | `bool` | Show logo on desktop |
| `displayLogoMobile` | `bool` | Show logo on mobile |
| `displayMenuDesktop` | `bool` | Show menu on desktop |
| `displayMenuMobile` | `bool` | Show menu on mobile |
| `colors` | `array` | `{bg, text, hover, active}` — menu color tokens |
| `logo` | `string\|null` | Path to logo image |
| `siteName` | `string\|null` | Site name for display |

---

### `iw_sulu_theme_block_styles()`

Returns the block style configuration (layout variations per block type).

```twig
{% set blockStyles = iw_sulu_theme_block_styles() %}
{% set textStyles = blockStyles.text.styles|default([]) %}
{# → [{key: 'one_column', label: '...', twig: '...', default: true}, ...] #}
```

**Returns:** `array` keyed by block type name, each containing a `styles` array.

---

### `iw_sulu_block_style_template(blockType, styleKey)`

Returns the Twig template path for a specific block style.

```twig
{# Get template for a specific style #}
{% set template = iw_sulu_block_style_template('text_images', 'overlay') %}

{# Get the default style template #}
{% set template = iw_sulu_block_style_template('gallery') %}

{% if template %}
    {% include template with { ... } %}
{% endif %}
```

**Parameters:**
- `blockType` (`string`) — Block type identifier (e.g. `text`, `text_images`, `gallery`)
- `styleKey` (`string|null`) — Style key. If `null`, returns the default style.

**Returns:** `string|null` — Twig template path, or `null` if not found.

---

## Global variable: `iw_sulu_theme`

Available everywhere in Twig without any import. Contains the same data as `iw_sulu_theme_tokens()`.

```twig
{# Access colors directly #}
{{ iw_sulu_theme.colors.primary }}

{# Access block variants #}
{% set variants = iw_sulu_theme.blockVariants|default([]) %}
{% set firstVariant = variants[0]|default({}) %}
{{ firstVariant.label }}

{# Access typography #}
{{ iw_sulu_theme.typography.baseFontSize }}
```

---

## Token structure

The `iw_sulu_theme` global (and `iw_sulu_theme_tokens()` return value) has this structure:

```
iw_sulu_theme
├── colors
│   ├── primary         → "#1a73e8"
│   ├── secondary       → "#34a853"
│   ├── accent          → "#fbbc04"
│   ├── background      → "#ffffff"
│   ├── text            → "#202124"
│   ├── link            → "#1a73e8"
│   ├── linkHover       → "#0d47a1"
│   └── border          → "#e5e7eb"
│
├── typography
│   ├── baseFontSize    → "16px"
│   ├── baseLineHeight  → "1.5"
│   ├── families[]
│   │   ├── {role: "body", name: "Inter", fallback: "sans-serif"}
│   │   ├── {role: "heading", name: "Poppins", fallback: "sans-serif"}
│   │   └── {role: "mono", name: "Roboto Mono", fallback: "monospace"}
│   └── scale
│       ├── xs → "0.75rem"
│       ├── sm → "0.875rem"
│       └── ...
│
├── buttons
│   ├── primary   → {bg, text, border, radius, hoverBg, hoverText, hoverBorder}
│   ├── secondary → {bg, text, border, radius, hoverBg, hoverText, hoverBorder}
│   └── accent    → {bg, text, border, radius, hoverBg, hoverText, hoverBorder}
│
├── borders
│   ├── radius → "0.5rem"
│   ├── width  → "1px"
│   └── color  → "#e5e7eb"
│
└── blockVariants[]
    ├── [0] → {label, title, subtitle, paragraph, link, linkHover, list, hr,
    │          blockBg, paragraphBg, buttonStyle, separatorMode, separatorStyle, separatorImage}
    ├── [1] → { ... }
    └── [2] → { ... }
```
