# CSS Custom Properties Reference

The SuluTailwindThemeBundle compiles your theme configuration into a single CSS file containing `:root` custom properties and utility classes. This file is served at `/iw-theme/css/theme-{id}-{hash}.css`.

## Including the theme CSS

```twig
{% set themeCssPath = iw_sulu_tailwind_theme_css_path() %}
{% if themeCssPath is not empty %}
    <link rel="stylesheet" href="{{ themeCssPath }}">
{% endif %}
```

---

## Color variables

Generated from **Settings > Themes > Colors** tab.

| Variable | Description | Example |
|----------|-------------|---------|
| `--color-primary` | Primary brand color | `#1a73e8` |
| `--color-secondary` | Secondary color | `#34a853` |
| `--color-accent` | Accent / highlight color | `#fbbc04` |
| `--color-background` | Page background | `#ffffff` |
| `--color-text` | Default text color | `#202124` |
| `--color-link` | Link color | `#1a73e8` |
| `--color-linkHover` | Link hover color | `#0d47a1` |
| `--color-border` | Default border color | `#e5e7eb` |

### Color palettes (OKLCH)

For each of the 4 main colors (`primary`, `secondary`, `accent`, `background`), 11 shades are generated using the OKLCH color space:

```css
--color-primary-50:  #eff6ff;
--color-primary-100: #dbeafe;
--color-primary-200: #bfdbfe;
--color-primary-300: #93c5fd;
--color-primary-400: #60a5fa;
--color-primary-500: #3b82f6;
--color-primary-600: #2563eb;
--color-primary-700: #1d4ed8;
--color-primary-800: #1e40af;
--color-primary-900: #1e3a8a;
--color-primary-950: #172554;
```

Same pattern for `--color-secondary-*`, `--color-accent-*`, `--color-background-*`.

**Usage example:**
```css
.my-card {
    background: var(--color-primary-50);
    border: 1px solid var(--color-primary-200);
}
.my-card:hover {
    background: var(--color-primary-100);
}
```

---

## Typography variables

Generated from **Settings > Themes > Typography** tab.

Font families are selected via the **Font Picker**, which supports three sources:
- **Google Fonts**: autocomplete from the synced catalog (requires [API key configuration](../README.md#google-fonts-api-key-optional))
- **System fonts**: 15 cross-platform fonts (Arial, Georgia, Courier New, etc.)
- **Free text**: manual entry (fallback when no API key is configured)

Only Google Fonts generate a `@import` rule in the compiled CSS. System fonts rely on the user's operating system.

### Font families

| Variable | Description | Example |
|----------|-------------|---------|
| `--font-family-heading` | Heading font family | `'Poppins', sans-serif` |
| `--font-family-body` | Body font family | `'Inter', sans-serif` |
| `--font-family-accent` | Accent font family (optional) | `'Playfair Display', serif` |

### Per-element variables

For each element (`h1`-`h6`, `body`, `link`), the following variables are generated from the typography assignments:

| Variable pattern | Description | Example |
|-----------------|-------------|---------|
| `--font-{el}-family` | Element font family reference | `var(--font-family-heading)` |
| `--font-{el}-weight` | Element font weight | `700` |
| `--font-size-{el}` | Element font size | `2.5rem` |
| `--font-{el}-style` | Element font style | `normal` |
| `--line-height-{el}` | Element line height | `1.2` |

Where `{el}` is `h1`, `h2`, `h3`, `h4`, `h5`, `h6`, `body`, or `link`.

**Full list of generated variables:**

| Variable | Default |
|----------|---------|
| `--font-h1-family` | `var(--font-family-heading)` |
| `--font-h1-weight` | `700` |
| `--font-size-h1` | `2.5rem` |
| `--font-h1-style` | `normal` |
| `--line-height-h1` | `1.2` |
| `--font-h2-weight` | `600` |
| `--font-size-h2` | `2rem` |
| `--font-h3-weight` | `600` |
| `--font-size-h3` | `1.5rem` |
| `--font-h4-weight` | `600` |
| `--font-size-h4` | `1.25rem` |
| `--font-h5-weight` | `500` |
| `--font-size-h5` | `1.125rem` |
| `--font-h6-weight` | `500` |
| `--font-size-h6` | `1rem` |
| `--font-body-family` | `var(--font-family-body)` |
| `--font-body-weight` | `400` |
| `--font-size-body` | `1rem` |
| `--font-body-style` | `normal` |
| `--line-height-body` | `1.5` |
| `--font-link-weight` | `500` |

### Base values

Derived from the `body` assignment:

| Variable | Description | Example |
|----------|-------------|---------|
| `--font-size-base` | Base font size (from body assignment) | `1rem` |
| `--line-height-base` | Base line height (from body assignment) | `1.5` |

### Font scale

| Variable | Value |
|----------|-------|
| `--font-size-xs` | `0.75rem` |
| `--font-size-sm` | `0.875rem` |
| `--font-size-base` | `1rem` |
| `--font-size-lg` | `1.125rem` |
| `--font-size-xl` | `1.25rem` |
| `--font-size-2xl` | `1.5rem` |
| `--font-size-3xl` | `1.875rem` |
| `--font-size-4xl` | `2.25rem` |

> The font family roles and scale values depend on the theme configuration.

**Usage example:**
```css
.my-heading {
    font-family: var(--font-h1-family, var(--font-family-heading));
    font-weight: var(--font-h1-weight, 700);
    font-size: var(--font-size-h1, 2.5rem);
    font-style: var(--font-h1-style, normal);
    line-height: var(--line-height-h1, 1.2);
}
.my-text {
    font-family: var(--font-body-family, var(--font-family-body));
    font-size: var(--font-size-base);
    line-height: var(--line-height-base);
}
```

---

## Border variables

Generated from **Settings > Themes > Borders** tab.

| Variable | Description | Example |
|----------|-------------|---------|
| `--border-radius` | Default border radius | `0.5rem` |
| `--border-width` | Default border width | `1px` |
| `--border-color` | Default border color | `#e5e7eb` |

**Usage example:**
```css
.my-card {
    border: var(--border-width) solid var(--border-color);
    border-radius: var(--border-radius);
}
```

---

## Button variables

Generated from **Settings > Themes > Buttons** tab. Three button variants are available: `primary`, `secondary`, `accent`.

| Variable pattern | Description |
|-----------------|-------------|
| `--btn-{variant}-bg` | Background color |
| `--btn-{variant}-text` | Text color |
| `--btn-{variant}-border` | Border color (or `none`) |
| `--btn-{variant}-radius` | Border radius |
| `--btn-{variant}-hoverBg` | Background on hover |
| `--btn-{variant}-hoverText` | Text color on hover |
| `--btn-{variant}-hoverBorder` | Border color on hover |

Where `{variant}` is `primary`, `secondary`, or `accent`.

**Usage example:**
```css
.my-custom-button {
    background-color: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border-radius: var(--btn-primary-radius);
}
.my-custom-button:hover {
    background-color: var(--btn-primary-hoverBg);
    color: var(--btn-primary-hoverText);
}
```

---

## Menu variables

Generated from **Settings > Themes > Menu** tab (colors section only).

| Variable | Description | Example |
|----------|-------------|---------|
| `--menu-bg` | Menu background | `#ffffff` |
| `--menu-text` | Menu text color | `#202124` |
| `--menu-hover` | Menu item hover bg | `#f3f3f3` |
| `--menu-active` | Active menu item color | `#1a73e8` |

---

## Button CSS classes

Ready-to-use button classes with hover transitions:

| Class | Description |
|-------|-------------|
| `.btn-primary` | Primary button style |
| `.btn-secondary` | Secondary button style |
| `.btn-accent` | Accent button style |

Each class includes `background-color`, `color`, `border`, `border-radius`, `cursor: pointer`, `display: inline-block`, `text-decoration: none` and a `transition`. Hover states are also generated.

**Usage in Twig:**
```twig
<a href="/contact" class="btn-primary inline-block px-6 py-3">Contact us</a>
<a href="/learn-more" class="btn-secondary inline-block px-6 py-3">Learn more</a>
```

---

## Block variant classes

See [Block variants documentation](block-variants.md) for the full reference on `.block-variant-*` classes and their internal CSS custom properties.
