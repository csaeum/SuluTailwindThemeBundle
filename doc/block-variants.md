# Block Variants

Block variants are per-section color schemes (e.g., light, accent, dark) defined in **Settings > Themes > Block variants**. They are stored as an indexed array: the array position (0, 1, 2...) is the identifier, making variants interchangeable across themes.

## How it works

1. Each variant is compiled into a `.block-variant-{index}` CSS class
2. When editing a page, the admin user picks a variant for each block
3. The chosen variant index is saved with the block data
4. On the frontend, the block wrapper applies the corresponding CSS class

## CSS custom properties per variant

Each `.block-variant-{index}` class sets these CSS custom properties:

| CSS custom property | Token key | Purpose |
|---------------------|-----------|---------|
| `--variant-title-color` | `title` | Color for h1-h6 elements |
| `--variant-subtitle-color` | `subtitle` | Color for `.block-subtitle` elements |
| `--variant-paragraph-color` | `paragraph` | Color for `<p>` elements |
| `--variant-link-color` | `link` | Color for links (excluding `.btn-*`) |
| `--variant-link-hover` | `linkHover` | Link hover color |
| `--variant-list-color` | `list` | Color for `<ul>` and `<ol>` |
| `--variant-hr-color` | `hr` | Color for `<hr>` separators |
| `--variant-paragraph-bg` | `paragraphBg` | Background for `.block-text` content |
| `--variant-subtle-bg` | *(computed)* | Subtle bg for code, table headers, blockquotes |

Additionally:
- `color` is set to the `title` value (default text color for the block)
- `background-color` is applied via `[data-has-bg="true"]` selector (only when the "Show background" checkbox is checked)

## Auto-styled elements inside a variant

The compiled CSS automatically styles these HTML elements inside any `.block-variant-*`:

| Element | Styling |
|---------|---------|
| `h1` - `h6` | `color: var(--variant-title-color)` |
| `.block-subtitle` | `color: var(--variant-subtitle-color)` |
| `p` | `color: var(--variant-paragraph-color)` |
| `a` (excluding `.btn-*`) | `color: var(--variant-link-color)`, hover: `var(--variant-link-hover)` |
| `ul`, `ol` | `color: var(--variant-list-color)` |
| `table` | Full styling with borders using `--variant-hr-color` |
| `table th` | Bold, `--variant-title-color` text, `--variant-subtle-bg` background |
| `code` (inline) | `--variant-subtle-bg` background, border |
| `pre` (code block) | `--variant-subtle-bg` background, border, padding |
| `blockquote` | Left border `--variant-hr-color`, italic, `--variant-subtitle-color` |
| `.todo-list` | Checkbox accent color from `--variant-link-color` |
| `hr` | Styled based on separator config (solid, dashed, gradient, wave...) |

## Variant-specific buttons

Each variant has a `buttonStyle` setting (primary, secondary, or accent). This generates a `.btn-variant` class scoped to the variant:

```css
.block-variant-0 .btn-variant { /* uses the chosen button style's colors */ }
.block-variant-0 .btn-variant:hover { /* hover state */ }
```

Use `.btn-variant` inside a block to automatically match the variant's button style:

```twig
<section class="block-variant-0">
    <a href="/cta" class="btn-variant px-6 py-3">Call to action</a>
</section>
```

## Paragraph background (`.block-text`)

When a variant's `paragraphBg` is set to a visible color (not empty, not `transparent`), the `.block-text` class inside that variant gets:

```css
.block-variant-0 .block-text {
    background-color: var(--variant-paragraph-bg);
    padding: 1rem 1.5rem;
    margin-block: 1rem;
    overflow: hidden;
}
.block-variant-0 .block-text:last-child {
    margin-bottom: 0;  /* prevents stacking with section padding */
}
```

## Using variants in custom Twig templates

### Basic usage

```twig
{# Resolve variant index with fallback #}
{% set variantIndex = variant|default(0) %}
{% set allVariants = iw_sulu_tailwind_theme.blockVariants|default([]) %}
{% if variantIndex >= allVariants|length %}
    {% set variantIndex = 0 %}
{% endif %}

<section class="block-variant-{{ variantIndex }}">
    <h2>This heading adapts to the variant colors</h2>
    <p>This paragraph too.</p>
    <a href="/link">And this link.</a>
</section>
```

### Reading variant configuration in Twig

You can access individual variant properties for custom logic:

```twig
{% set variantConfig = iw_sulu_tailwind_theme.blockVariants[variantIndex]|default({}) %}

{# Read specific values #}
{% set titleColor = variantConfig.title|default('#000') %}
{% set bgColor = variantConfig.blockBg|default('#fff') %}
{% set buttonStyle = variantConfig.buttonStyle|default('primary') %}
{% set separatorMode = variantConfig.separatorMode|default('style') %}
```

### Using the block wrapper partial

The bundle provides `@ItechWorldSuluTailwindTheme/blocks/common/_block_wrapper.html.twig` that handles all the variant/margin/padding/container logic. Use it in your own block templates:

```twig
{# my_custom_block.html.twig #}
{% embed '@ItechWorldSuluTailwindTheme/blocks/common/_block_wrapper.html.twig' with {
    variant: block.variant|default(0),
    marginTop: block.marginTop|default('mt-5'),
    marginBottom: block.marginBottom|default('mb-5'),
    paddingTop: block.paddingTop|default('pt-3'),
    paddingBottom: block.paddingBottom|default('pb-3'),
    paddingLateral: block.paddingLateral|default('px-3'),
    lateralMargins: block.lateralMargins|default('exterior'),
    blockRadius: block.blockRadius|default(''),
    showBackground: block.showBackground|default(true),
    paragraphImageRadius: block.paragraphImageRadius|default(''),
} %}
    {% block block_content %}
        {# Your custom block content here #}
        <h2>{{ block.title }}</h2>
        <div class="block-text prose max-w-none">
            {{ block.text|raw }}
        </div>
    {% endblock %}
{% endembed %}
```

## Separator styles

Each variant can have a different separator style. Three modes are available:

| Mode | CSS behavior | Twig behavior |
|------|-------------|---------------|
| `style` (default) | `<hr>` is styled via CSS (solid, dashed, dotted, double, gradient, wave, zigzag, dots, diamond) | Just render `<hr>` |
| `image` | `<hr>` is hidden via CSS | Twig renders a custom `<img>` separator |
| `none` | Both `<hr>` and `.block-separator` are hidden | Nothing rendered |

Available CSS separator styles: `solid`, `dashed`, `dotted`, `double`, `gradient`, `wave`, `zigzag`, `dots`, `diamond`.
