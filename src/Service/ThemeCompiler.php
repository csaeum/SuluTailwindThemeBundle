<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Service;

use ItechWorld\SuluTailwindThemeBundle\Entity\ThemeConfig;

/**
 * Compiles ThemeConfig design tokens into CSS custom properties.
 *
 * Generates a CSS file containing:
 * - Google Fonts import (if configured)
 * - CSS custom properties on :root from design tokens
 * - Block variant utility classes
 * - Button style classes
 * - Menu CSS variables
 */
class ThemeCompiler
{
    /**
     * Mapping from Tailwind rounded-* class suffixes to CSS border-radius values.
     */
    private const RADIUS_MAP = [
        'rounded-none' => '0',
        'rounded-xs' => '0.125rem',
        'rounded-sm' => '0.25rem',
        'rounded-md' => '0.375rem',
        'rounded-lg' => '0.5rem',
        'rounded-xl' => '0.75rem',
        'rounded-2xl' => '1rem',
        'rounded-3xl' => '1.5rem',
        'rounded-4xl' => '2rem',
        'rounded-full' => 'calc(infinity * 1px)',
    ];

    public function __construct(
        private readonly string $cssOutputDir,
        private readonly GoogleFontsResolver $googleFontsResolver,
        private readonly OklchPaletteGenerator $paletteGenerator,
    ) {
    }

    /**
     * Convert a Tailwind rounded-* class to a CSS border-radius value.
     *
     * Falls back to returning the raw value if it does not match a known class
     * (for backwards compatibility with older "8px" / "0.5rem" values).
     */
    private function resolveRadius(string $value): string
    {
        return self::RADIUS_MAP[$value] ?? $value;
    }

    /**
     * Compile a theme configuration into a CSS file.
     *
     * Generates CSS custom properties from design tokens, writes the output
     * to a versioned file, and returns the absolute file path.
     *
     * @param ThemeConfig $theme The theme configuration to compile
     *
     * @return string The absolute path to the generated CSS file
     *
     * @throws \RuntimeException If the output directory cannot be created
     */
    public function compile(ThemeConfig $theme): string
    {
        $this->ensureOutputDir();
        $this->invalidate($theme);

        $css = $this->generateCss($theme);
        $filePath = $this->buildFilePath($theme);

        file_put_contents($filePath, $css);

        return $filePath;
    }

    /**
     * Get the web-accessible CSS path for a theme.
     *
     * Looks for the actual compiled file on disk rather than computing
     * the filename from updatedAt, to avoid hash mismatches caused by
     * DateTime precision differences between compile-time and render-time.
     *
     * If no compiled file is found, triggers a compilation automatically.
     *
     * @param ThemeConfig $theme The theme configuration
     *
     * @return string The web-accessible path (e.g. "/iw-theme/css/theme-1-abc123.css"),
     *                or empty string if compilation fails
     */
    public function getCssPath(ThemeConfig $theme): string
    {
        if (null === $theme->getId()) {
            return '';
        }

        $pattern = $this->cssOutputDir . '/theme-' . $theme->getId() . '-*.css';
        $files = glob($pattern);

        // Auto-compile if no file found
        if (empty($files)) {
            $this->compile($theme);
            $files = glob($pattern);
        }

        if (empty($files) || false === $files) {
            return '';
        }

        return '/iw-theme/css/' . basename(end($files));
    }

    /**
     * Invalidate (delete) old compiled CSS files for a theme.
     *
     * Removes all previously compiled CSS files matching the theme ID pattern,
     * ensuring stale cached files are cleaned up.
     *
     * @param ThemeConfig $theme The theme whose CSS files should be removed
     */
    public function invalidate(ThemeConfig $theme): void
    {
        if (null === $theme->getId()) {
            return;
        }

        $pattern = $this->cssOutputDir . '/theme-' . $theme->getId() . '-*.css';
        $files = glob($pattern);

        if (false === $files) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Generate the complete CSS content for a theme.
     *
     * @param ThemeConfig $theme The theme to generate CSS for
     *
     * @return string The complete CSS content
     */
    private function generateCss(ThemeConfig $theme): string
    {
        $tokens = $theme->getTokens();
        $menuConfig = $theme->getMenuConfig();
        $css = "/* Theme: {$theme->getLabel()} — Auto-generated, do not edit */\n\n";

        // Google Fonts import
        $typography = $tokens['typography'] ?? [];
        $fontsUrl = $this->googleFontsResolver->resolve($typography);
        if (null !== $fontsUrl) {
            $css .= "@import url('{$fontsUrl}');\n\n";
        }

        // :root CSS custom properties
        $css .= ":root {\n";
        $css .= $this->generateColorVariables($tokens['colors'] ?? []);
        $css .= $this->generatePaletteVariables($tokens['colors'] ?? []);
        $css .= $this->generateTypographyVariables($typography);
        $css .= $this->generateBorderVariables($tokens['borders'] ?? []);
        $css .= $this->generateButtonVariables($tokens['buttons'] ?? []);
        $css .= $this->generateMenuVariables($menuConfig);
        $css .= "}\n\n";

        // Button classes
        $css .= $this->generateButtonClasses($tokens['buttons'] ?? []);

        // Menu utility classes (navbar, dropdowns, overlay, social icons)
        $css .= $this->generateMenuClasses();

        // Block variant classes
        $css .= $this->generateBlockVariantClasses($tokens['blockVariants'] ?? [], $tokens['buttons'] ?? []);

        return $css;
    }

    /**
     * Generate CSS custom properties for color tokens.
     *
     * @param array<string, mixed> $colors Color token values
     *
     * @return string CSS variable declarations
     */
    private function generateColorVariables(array $colors): string
    {
        $css = "  /* Colors */\n";

        foreach ($colors as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $css .= "  --color-{$key}-{$subKey}: {$subValue};\n";
                }
            } else {
                $css .= "  --color-{$key}: {$value};\n";
            }
        }

        return $css . "\n";
    }

    /**
     * Generate CSS custom properties for OKLCH color palettes.
     *
     * For each of the 4 main colors (primary, secondary, accent, background),
     * generates 11 shades (50→950) as CSS custom properties using the OKLCH
     * color space for perceptually uniform results.
     *
     * @param array<string, mixed> $colors Color token values
     *
     * @return string CSS variable declarations (e.g. --color-primary-50: #eff6ff;)
     */
    private function generatePaletteVariables(array $colors): string
    {
        $paletteColors = ['primary', 'secondary', 'accent', 'background'];
        $css = "  /* Color palettes (OKLCH) */\n";
        $hasAny = false;

        foreach ($paletteColors as $colorName) {
            $hex = $colors[$colorName] ?? null;
            if (!is_string($hex) || $hex === '') {
                continue;
            }

            $hasAny = true;
            $palette = $this->paletteGenerator->generatePalette($hex);

            foreach ($palette as $shade => $shadeHex) {
                $css .= "  --color-{$colorName}-{$shade}: {$shadeHex};\n";
            }
        }

        if (!$hasAny) {
            return '';
        }

        return $css . "\n";
    }

    /**
     * Default values for each typography assignment element.
     *
     * Used as fallback when assignment data is missing for an element.
     */
    private const TYPO_DEFAULTS = [
        'h1' => ['family' => 'heading', 'weight' => '700', 'size' => 2.5, 'style' => 'normal', 'lineHeight' => '1.2'],
        'h2' => ['family' => 'heading', 'weight' => '600', 'size' => 2, 'style' => 'normal', 'lineHeight' => '1.25'],
        'h3' => ['family' => 'heading', 'weight' => '600', 'size' => 1.5, 'style' => 'normal', 'lineHeight' => '1.3'],
        'h4' => ['family' => 'heading', 'weight' => '600', 'size' => 1.25, 'style' => 'normal', 'lineHeight' => '1.35'],
        'h5' => ['family' => 'heading', 'weight' => '500', 'size' => 1.125, 'style' => 'normal', 'lineHeight' => '1.4'],
        'h6' => ['family' => 'heading', 'weight' => '500', 'size' => 1, 'style' => 'normal', 'lineHeight' => '1.4'],
        'body' => ['family' => 'body', 'weight' => '400', 'size' => 1, 'style' => 'normal', 'lineHeight' => '1.5'],
        'link' => ['family' => 'body', 'weight' => '500', 'size' => 1, 'style' => 'normal', 'lineHeight' => '1.5'],
    ];

    /**
     * Generate CSS custom properties for typography tokens.
     *
     * Generates:
     * - Font family variables (--font-family-heading, --font-family-body, --font-family-accent)
     * - Per-element assignment variables (--font-h1-family, --font-h1-weight, --font-size-h1, etc.)
     * - Base values derived from body assignment (--font-size-base, --line-height-base)
     * - Scale variables (--font-size-xs, --font-size-sm, etc.)
     *
     * @param array<string, mixed> $typography Typography token values
     *
     * @return string CSS variable declarations
     */
    private function generateTypographyVariables(array $typography): string
    {
        $css = "  /* Typography — Font families */\n";

        // Font family variables
        $families = $typography['families'] ?? [];
        foreach ($families as $family) {
            $role = $family['role'] ?? 'body';
            $name = $family['name'] ?? 'sans-serif';
            $fallback = $family['fallback'] ?? 'sans-serif';
            $css .= "  --font-family-{$role}: '{$name}', {$fallback};\n";
        }

        // Assignment variables per element
        $assignments = $typography['assignments'] ?? [];
        $css .= "\n  /* Typography — Assignments */\n";

        foreach (self::TYPO_DEFAULTS as $element => $defaults) {
            $props = array_merge($defaults, $assignments[$element] ?? []);
            $familyRole = $props['family'];
            $weight = $props['weight'];
            $size = $this->normalizeFontSize($props['size']);
            $style = $props['style'];
            $lineHeight = $props['lineHeight'];

            $css .= "  --font-{$element}-family: var(--font-family-{$familyRole});\n";
            $css .= "  --font-{$element}-weight: {$weight};\n";
            $css .= "  --font-size-{$element}: {$size};\n";
            $css .= "  --font-{$element}-style: {$style};\n";
            $css .= "  --line-height-{$element}: {$lineHeight};\n";
        }

        // Base values derived from body assignment (backwards compatible)
        $bodyProps = array_merge(self::TYPO_DEFAULTS['body'], $assignments['body'] ?? []);
        $baseFontSize = $this->normalizeFontSize($bodyProps['size'] ?? $typography['baseFontSize'] ?? '16px');
        $baseLineHeight = $bodyProps['lineHeight'] ?? $typography['baseLineHeight'] ?? '1.5';
        $css .= "\n  /* Typography — Base values */\n";
        $css .= "  --font-size-base: {$baseFontSize};\n";
        $css .= "  --line-height-base: {$baseLineHeight};\n";

        // Scale
        $scale = $typography['scale'] ?? [];
        if (!empty($scale)) {
            $css .= "\n  /* Typography — Scale */\n";
            foreach ($scale as $key => $value) {
                $css .= "  --font-size-{$key}: {$value};\n";
            }
        }

        return $css . "\n";
    }

    /**
     * Normalize a font size value to include the "rem" unit.
     *
     * Handles both legacy string values (e.g. "2.5rem") and numeric values
     * from the number form field (e.g. 2.5 or "2.5").
     *
     * @param string|int|float $value Raw font size value
     *
     * @return string Normalized value with CSS unit (e.g. "2.5rem")
     */
    private function normalizeFontSize(string|int|float $value): string
    {
        $stringValue = (string) $value;

        // Already has a CSS unit (rem, px, em, etc.) — return as-is
        if (preg_match('/[a-z%]+$/i', $stringValue)) {
            return $stringValue;
        }

        // Pure numeric value — append "rem"
        return $stringValue . 'rem';
    }

    /**
     * Generate CSS custom properties for border tokens.
     *
     * @param array<string, mixed> $borders Border token values
     *
     * @return string CSS variable declarations
     */
    private function generateBorderVariables(array $borders): string
    {
        $css = "  /* Borders */\n";

        foreach ($borders as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $resolved = str_starts_with((string) $subValue, 'rounded-') ? $this->resolveRadius((string) $subValue) : $subValue;
                    $css .= "  --border-{$key}-{$subKey}: {$resolved};\n";
                }
            } else {
                $resolved = str_starts_with((string) $value, 'rounded-') ? $this->resolveRadius((string) $value) : $value;
                $css .= "  --border-{$key}: {$resolved};\n";
            }
        }

        return $css . "\n";
    }

    /**
     * Generate CSS custom properties for button tokens.
     *
     * @param array<string, mixed> $buttons Button token values
     *
     * @return string CSS variable declarations
     */
    private function generateButtonVariables(array $buttons): string
    {
        $css = "  /* Buttons */\n";

        foreach ($buttons as $variant => $props) {
            if (!is_array($props)) {
                continue;
            }
            foreach ($props as $prop => $value) {
                // Resolve radius Tailwind classes to valid CSS values
                if ('radius' === $prop) {
                    $value = $this->resolveRadius((string) $value);
                }
                $css .= "  --btn-{$variant}-{$prop}: {$value};\n";
            }
        }

        return $css . "\n";
    }

    /**
     * Generate CSS custom properties for menu colors.
     *
     * Only the "colors" sub-object of menuConfig generates CSS variables.
     * Other keys (type, animation, childLevels, display options) are
     * configuration values consumed by Twig, not CSS.
     *
     * @param array<string, mixed> $menuConfig Menu configuration values
     *
     * @return string CSS variable declarations
     */
    private function generateMenuVariables(array $menuConfig): string
    {
        $css = "  /* Menu colors */\n";

        $colors = $menuConfig['colors'] ?? [];
        foreach ($colors as $key => $value) {
            if (!is_array($value)) {
                $css .= "  --menu-{$key}: {$value};\n";
            }
        }

        return $css . "\n";
    }

    /**
     * Generate CSS utility classes for the menu component.
     *
     * Covers: navbar base, text colors per level, dropdown backgrounds,
     * dividers, burger icons, logo sizing, fullscreen overlay, and
     * social media icons (mask-image technique for SVG coloring).
     *
     * @return string CSS class declarations
     */
    private function generateMenuClasses(): string
    {
        $css = "/* Menu component */\n";

        // Base: navbar header + overlay background/text
        $css .= ".iw-menu { background-color: var(--menu-bg); color: var(--menu-text); }\n";
        $css .= ".iw-menu > nav { background-color: inherit; }\n";

        // Transparent navbar variant
        $css .= ".iw-menu.iw-menu-transparent { background-color: transparent; }\n";

        // Text colors per navigation level (with hover transition)
        $css .= ".iw-menu-text { color: var(--menu-text); transition: color 0.2s ease; }\n";
        $css .= ".iw-menu-text:hover { color: var(--menu-textHover, var(--menu-text)); }\n";
        $css .= ".iw-menu-text-l2 { color: var(--menu-secondText, var(--menu-text)); transition: color 0.2s ease; }\n";
        $css .= ".iw-menu-text-l2:hover { color: var(--menu-secondTextHover, var(--menu-secondText, var(--menu-text))); }\n";
        $css .= ".iw-menu-text-l3 { color: var(--menu-thirdText, var(--menu-secondText, var(--menu-text))); transition: color 0.2s ease; }\n";
        $css .= ".iw-menu-text-l3:hover { color: var(--menu-thirdTextHover, var(--menu-thirdText, var(--menu-secondText, var(--menu-text)))); }\n";

        // Dropdown backgrounds per level
        $css .= ".iw-menu-dropdown-l2 { background-color: var(--menu-secondBg, var(--menu-bg)); border-radius: var(--border-radius); }\n";
        $css .= ".iw-menu-dropdown-l3 { background-color: var(--menu-thirdBg, var(--menu-secondBg, var(--menu-bg))); border-radius: var(--border-radius); }\n";

        // Dividers
        $css .= ".iw-menu-divider { border-color: var(--menu-divider, rgba(255,255,255,0.1)); }\n";

        // Animated burger button (3 lines → X)
        $css .= ".iw-menu-burger { color: var(--menu-burgerOpen, var(--menu-text)); }\n";
        $css .= ".iw-menu-burger-line {\n";
        $css .= "  display: block;\n";
        $css .= "  width: 22px;\n";
        $css .= "  height: 2px;\n";
        $css .= "  background-color: currentColor;\n";
        $css .= "  transition: transform 0.3s ease, opacity 0.3s ease;\n";
        $css .= "}\n";
        $css .= ".iw-menu-burger.is-open { color: var(--menu-burgerClose, var(--menu-text)); }\n";
        $css .= ".iw-menu-burger.is-open .iw-menu-burger-line:nth-child(1) { transform: translateY(8px) rotate(45deg); }\n";
        $css .= ".iw-menu-burger.is-open .iw-menu-burger-line:nth-child(2) { opacity: 0; }\n";
        $css .= ".iw-menu-burger.is-open .iw-menu-burger-line:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }\n";

        // Logo sizing
        $css .= ".iw-menu-logo-desktop { max-height: 40px; }\n";
        $css .= ".iw-menu-logo-mobile { max-height: 32px; }\n";

        // Fullscreen overlay (transition is handled by JS, not CSS, to avoid conflicts)
        $css .= ".iw-menu-overlay {\n";
        $css .= "  background-color: var(--menu-bg);\n";
        $css .= "  color: var(--menu-text);\n";
        $css .= "}\n";
        $css .= ".iw-menu-overlay-nav { height: 100%; }\n";

        // Fullscreen split layout (curtain effect)
        $css .= ".iw-menu-fullscreen-nav { background-color: var(--menu-bg); }\n";

        // Sidebar panel
        $css .= ".iw-menu-sidebar { background-color: var(--menu-bg); }\n";

        // Backdrop overlay
        $css .= ".iw-menu-backdrop { background-color: rgba(0, 0, 0, 0.5); }\n";

        // Social media icons — mask-image technique for SVG coloring
        $css .= ".iw-social-icon {\n";
        $css .= "  display: inline-block;\n";
        $css .= "  background-color: var(--menu-socialMedia);\n";
        $css .= "  -webkit-mask-size: contain;\n";
        $css .= "  mask-size: contain;\n";
        $css .= "  -webkit-mask-repeat: no-repeat;\n";
        $css .= "  mask-repeat: no-repeat;\n";
        $css .= "  -webkit-mask-position: center;\n";
        $css .= "  mask-position: center;\n";
        $css .= "  transition: background-color 0.2s ease;\n";
        $css .= "}\n";
        $css .= "a:hover > .iw-social-icon { background-color: var(--menu-socialMediaHover, var(--menu-socialMedia)); }\n";
        $css .= ".iw-social-text { color: var(--menu-socialMedia); transition: color 0.2s ease; }\n";
        $css .= "a:hover > .iw-social-text { color: var(--menu-socialMediaHover, var(--menu-socialMedia)); }\n\n";

        // Mega menu dropdown panel
        $css .= ".iw-mega-dropdown { background-color: var(--menu-secondBg, var(--menu-bg)); ";
        $css .= "border-top: 1px solid var(--menu-divider, rgba(0,0,0,0.1)); }\n";
        // Mega menu featured column
        $css .= ".iw-mega-featured { background-color: var(--menu-thirdBg, var(--menu-secondBg, var(--menu-bg))); ";
        $css .= "border-radius: var(--border-radius); padding: 1.5rem; }\n";
        // Mega menu image card (radius by default for consistent hover shadow)
        $css .= ".iw-mega-card { border-radius: var(--border-radius); overflow: hidden; ";
        $css .= "transition: transform 0.2s ease, box-shadow 0.2s ease; }\n";
        $css .= ".iw-mega-card:hover { transform: translateY(-2px); ";
        $css .= "box-shadow: 0 4px 12px rgba(0,0,0,0.1); }\n";
        // Mega menu card image (uses theme image radius by default)
        $css .= ".iw-mega-card img { width: 100%; height: auto; object-fit: cover; ";
        $css .= "border-radius: var(--border-imageRadius, var(--border-radius)); }\n";
        // Mega menu card with background: radius on card, overflow clips image corners, no image radius
        $css .= ".iw-mega-card-bg { background-color: var(--menu-thirdBg, var(--menu-secondBg, var(--menu-bg))); ";
        $css .= "border-radius: var(--border-radius); overflow: hidden; }\n";
        $css .= ".iw-mega-card-bg img { border-radius: 0; }\n";
        // Mega menu featured image (uses theme image radius)
        $css .= ".iw-mega-featured img { width: 100%; height: auto; object-fit: cover; ";
        $css .= "border-radius: var(--border-imageRadius, var(--border-radius)); }\n";
        // Mega menu column grid (1 to 5 columns) with responsive breakpoints
        for ($i = 1; $i <= 5; $i++) {
            $css .= ".iw-mega-grid-{$i} { display: grid; gap: 2rem; ";
            $css .= "grid-template-columns: repeat({$i}, 1fr); }\n";
        }
        // Responsive: 3 columns → 2 under 900px
        $css .= "@media (max-width: 900px) {\n";
        $css .= "  .iw-mega-grid-3 { grid-template-columns: repeat(2, 1fr); }\n";
        $css .= "}\n";
        // Responsive: 4-5 columns → 2 under 1024px, then 1 under 768px
        $css .= "@media (max-width: 1024px) {\n";
        $css .= "  .iw-mega-grid-4 { grid-template-columns: repeat(2, 1fr); }\n";
        $css .= "  .iw-mega-grid-5 { grid-template-columns: repeat(2, 1fr); }\n";
        $css .= "}\n";
        $css .= "@media (max-width: 768px) {\n";
        $css .= "  .iw-mega-grid-3,\n";
        $css .= "  .iw-mega-grid-4,\n";
        $css .= "  .iw-mega-grid-5 { grid-template-columns: 1fr; }\n";
        $css .= "}\n";

        // Mega menu horizontal card layout (image + text side by side)
        $css .= ".iw-mega-card-horizontal { display: flex; align-items: center; }\n";
        $css .= ".iw-mega-card-horizontal img { width: 40%; flex-shrink: 0; }\n";
        $css .= ".iw-mega-card-horizontal .iw-mega-card-body { flex: 1; }\n";
        $css .= ".iw-mega-card-img-right { flex-direction: row-reverse; }\n";
        // Mega menu horizontal featured layout (image + content side by side)
        $css .= ".iw-mega-featured-horizontal { display: flex; align-items: flex-start; gap: 1.5rem; }\n";
        $css .= ".iw-mega-featured-horizontal img { width: 45%; flex-shrink: 0; }\n";
        $css .= ".iw-mega-featured-horizontal .iw-mega-featured-body { flex: 1; }\n";
        $css .= ".iw-mega-featured-img-right { flex-direction: row-reverse; }\n";
        $css .= "\n";

        return $css;
    }

    /**
     * Generate CSS classes for button variants.
     *
     * @param array<string, mixed> $buttons Button token values
     *
     * @return string CSS class declarations
     */
    private function generateButtonClasses(array $buttons): string
    {
        $css = "/* Button classes */\n";

        foreach ($buttons as $variant => $props) {
            if (!is_array($props)) {
                continue;
            }
            $css .= ".btn-{$variant} {\n";
            if (isset($props['bg'])) {
                $css .= "  background-color: {$props['bg']};\n";
            }
            if (isset($props['text'])) {
                $css .= "  color: {$props['text']};\n";
            }
            if (isset($props['radius'])) {
                $css .= "  border-radius: {$this->resolveRadius((string) $props['radius'])};\n";
            }
            if (isset($props['border']) && 'none' !== $props['border']) {
                $css .= "  border: 1px solid {$props['border']};\n";
            } else {
                $css .= "  border: none;\n";
            }
            $css .= "  cursor: pointer;\n";
            $css .= "  display: inline-block;\n";
            $css .= "  text-decoration: none;\n";
            $css .= "  transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;\n";
            $css .= "}\n";

            // Hover state
            $css .= ".btn-{$variant}:hover {\n";
            if (isset($props['hoverBg'])) {
                $css .= "  background-color: {$props['hoverBg']};\n";
            }
            if (isset($props['hoverText'])) {
                $css .= "  color: {$props['hoverText']};\n";
            }
            if (isset($props['hoverBorder']) && 'none' !== $props['hoverBorder']) {
                $css .= "  border-color: {$props['hoverBorder']};\n";
            }
            $css .= "}\n\n";
        }

        return $css;
    }

    /**
     * Generate CSS classes for block variants.
     *
     * Each variant generates a `.block-variant-{index}` class with CSS custom
     * properties for title, subtitle, paragraph, link, list, hr, paragraphBg
     * and blockBg colors. Templates use these properties for consistent styling.
     *
     * Variants are stored as an indexed array; the array position (0, 1, 2...)
     * is the identifier, making variants interchangeable between themes.
     *
     * @param array<int, array<string, mixed>> $blockVariants Block variant definitions (indexed)
     * @param array<string, mixed>             $buttons       Button variant definitions (for .btn-variant mapping)
     *
     * @return string CSS class declarations
     */
    private function generateBlockVariantClasses(array $blockVariants, array $buttons = []): string
    {
        $css = "/* Block variant classes */\n";

        // Map from token keys to CSS custom property names
        $propertyMap = [
            'blockBg' => 'background-color',
            'title' => '--variant-title-color',
            'subtitle' => '--variant-subtitle-color',
            'paragraph' => '--variant-paragraph-color',
            'link' => '--variant-link-color',
            'linkHover' => '--variant-link-hover',
            'list' => '--variant-list-color',
            'hr' => '--variant-hr-color',
            'paragraphBg' => '--variant-paragraph-bg',
        ];

        foreach ($blockVariants as $index => $props) {
            if (!is_array($props)) {
                continue;
            }
            $css .= ".block-variant-{$index} {\n";

            foreach ($propertyMap as $tokenKey => $cssProperty) {
                // blockBg is handled separately with the [data-has-bg] selector
                if ($tokenKey === 'blockBg') {
                    continue;
                }
                if (!isset($props[$tokenKey])) {
                    continue;
                }
                $css .= "  {$cssProperty}: {$props[$tokenKey]};\n";
            }

            // Apply title color as default text color for the block
            if (isset($props['title'])) {
                $css .= "  color: {$props['title']};\n";
            }

            // Subtle background for code, table headers, blockquotes
            $blockBgHex = trim((string) ($props['blockBg'] ?? '#ffffff'));
            $subtleBg = $this->isLightBackground($blockBgHex)
                ? 'rgba(0,0,0,0.04)'
                : 'rgba(255,255,255,0.07)';
            $css .= "  --variant-subtle-bg: {$subtleBg};\n";

            $css .= "}\n";

            // Block background only visible when showBackground is checked (data-has-bg)
            if (!empty($props['blockBg'])) {
                $css .= ".block-variant-{$index}[data-has-bg=\"true\"] {\n";
                $css .= "  background-color: {$props['blockBg']};\n";
                $css .= "}\n";
            }

            // Child element selectors using custom properties
            $css .= ".block-variant-{$index} h1,\n";
            $css .= ".block-variant-{$index} h2,\n";
            $css .= ".block-variant-{$index} h3,\n";
            $css .= ".block-variant-{$index} h4,\n";
            $css .= ".block-variant-{$index} h5,\n";
            $css .= ".block-variant-{$index} h6 {\n";
            $css .= "  color: var(--variant-title-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} .block-subtitle {\n";
            $css .= "  color: var(--variant-subtitle-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} p {\n";
            $css .= "  color: var(--variant-paragraph-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} a:not([class*=\"btn-\"]) {\n";
            $css .= "  color: var(--variant-link-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} a:not([class*=\"btn-\"]):hover {\n";
            $css .= "  color: var(--variant-link-hover, var(--variant-link-color, inherit));\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} ul,\n";
            $css .= ".block-variant-{$index} ol {\n";
            $css .= "  color: var(--variant-list-color, inherit);\n";
            $css .= "}\n";

            // List bottom margin inside block-text
            $css .= ".block-variant-{$index} .block-text ul,\n";
            $css .= ".block-variant-{$index} .block-text ol {\n";
            $css .= "  margin-bottom: 1em;\n";
            $css .= "}\n";

            // Table styling (CKEditor wraps in <figure class="table">)
            $css .= ".block-variant-{$index} figure.table {\n";
            $css .= "  margin: 1rem 0;\n";
            $css .= "  overflow-x: auto;\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} table {\n";
            $css .= "  width: 100%;\n";
            $css .= "  border-collapse: collapse;\n";
            $css .= "  color: var(--variant-paragraph-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} table th,\n";
            $css .= ".block-variant-{$index} table td {\n";
            $css .= "  padding: 0.75rem 1rem;\n";
            $css .= "  border: 1px solid var(--variant-hr-color, #e5e7eb);\n";
            $css .= "  text-align: left;\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} table th {\n";
            $css .= "  font-weight: 600;\n";
            $css .= "  color: var(--variant-title-color, inherit);\n";
            $css .= "  background-color: var(--variant-subtle-bg);\n";
            $css .= "}\n";

            // Inline code (<code> not inside <pre>)
            $css .= ".block-variant-{$index} :not(pre) > code {\n";
            $css .= "  background-color: var(--variant-subtle-bg);\n";
            $css .= "  padding: 0.15em 0.4em;\n";
            $css .= "  border-radius: 4px;\n";
            $css .= "  font-size: 0.875em;\n";
            $css .= "  border: 1px solid var(--variant-hr-color, #e5e7eb);\n";
            $css .= "}\n";

            // Code blocks (<pre><code>)
            $css .= ".block-variant-{$index} pre {\n";
            $css .= "  background-color: var(--variant-subtle-bg);\n";
            $css .= "  padding: 1rem 1.25rem;\n";
            $css .= "  border-radius: var(--border-radius, 8px);\n";
            $css .= "  overflow-x: auto;\n";
            $css .= "  margin: 1rem 0;\n";
            $css .= "  border: 1px solid var(--variant-hr-color, #e5e7eb);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} pre code {\n";
            $css .= "  background: none;\n";
            $css .= "  padding: 0;\n";
            $css .= "  border-radius: 0;\n";
            $css .= "  border: none;\n";
            $css .= "  font-size: 0.875em;\n";
            $css .= "  color: var(--variant-paragraph-color, inherit);\n";
            $css .= "}\n";

            // Blockquote
            $css .= ".block-variant-{$index} blockquote {\n";
            $css .= "  border-left: 4px solid var(--variant-hr-color, #e5e7eb);\n";
            $css .= "  padding: 0.5rem 0 0.5rem 1rem;\n";
            $css .= "  margin: 1rem 0;\n";
            $css .= "  color: var(--variant-subtitle-color, inherit);\n";
            $css .= "  font-style: italic;\n";
            $css .= "}\n";

            // To-do list (CKEditor <ul class="todo-list">)
            $css .= ".block-variant-{$index} .todo-list {\n";
            $css .= "  list-style: none;\n";
            $css .= "  padding-left: 0;\n";
            $css .= "  color: var(--variant-list-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} .todo-list .todo-list__label {\n";
            $css .= "  display: flex;\n";
            $css .= "  align-items: flex-start;\n";
            $css .= "  gap: 0.5rem;\n";
            $css .= "}\n";

            $css .= ".block-variant-{$index} .todo-list input[type=\"checkbox\"] {\n";
            $css .= "  margin-top: 0.25em;\n";
            $css .= "  accent-color: var(--variant-link-color, var(--color-primary, currentColor));\n";
            $css .= "}\n";

            $css .= $this->generateSeparatorCss((string) $index, $props);
            $css .= $this->generateVariantButtonCss((string) $index, $props, $buttons);

            // Apply paragraph background + padding only when paragraphBg is a real
            // visible color (not empty, not "transparent").
            // Vertical margin only (margin-block): lateral margin is handled by the
            // template (mx-4) when the block has no lateral padding — adding it here
            // would stack with the block's own paddingLateral.
            // No visible paragraphBg → no background, no padding, no margin.
            $pgBg = trim($props['paragraphBg'] ?? '');
            if ($pgBg !== '' && strtolower($pgBg) !== 'transparent') {
                $css .= ".block-variant-{$index} .block-text {\n";
                $css .= "  background-color: var(--variant-paragraph-bg);\n";
                $css .= "  padding: 1rem 1.5rem;\n";
                $css .= "  margin-block: 1rem;\n";
                $css .= "  overflow: hidden;\n";
                $css .= "}\n";
                // Remove bottom margin when block-text is the last child
                // to prevent it from stacking with the section's padding-bottom
                // or overflowing when pb is 0.
                $css .= ".block-variant-{$index} .block-text:last-child {\n";
                $css .= "  margin-bottom: 0;\n";
                $css .= "}\n";
                // Hide the dark overlay on background images when paragraph has its own bg
                $css .= ".block-variant-{$index} .block-bg-overlay {\n";
                $css .= "  display: none;\n";
                $css .= "}\n";
            }

            $css .= "\n";
        }

        return $css;
    }

    /**
     * Generate CSS for variant-specific button styling.
     *
     * Reads the variant's buttonStyle choice (primary, secondary, accent) and
     * generates a `.btn-variant` class with the chosen button's direct values.
     * Uses the same resolution logic as generateButtonClasses() to ensure
     * radius and border properties are correctly handled.
     *
     * @param string               $variantName The variant key
     * @param array<string, mixed> $props       The variant properties
     * @param array<string, mixed> $buttons     All button variant definitions
     *
     * @return string CSS declarations
     */
    private function generateVariantButtonCss(string $variantName, array $props, array $buttons): string
    {
        $buttonStyle = $props['buttonStyle'] ?? 'primary';
        $allowed = ['primary', 'secondary', 'accent'];
        if (!in_array($buttonStyle, $allowed, true)) {
            $buttonStyle = 'primary';
        }

        $btnData = $buttons[$buttonStyle] ?? [];
        if (empty($btnData) || !is_array($btnData)) {
            return '';
        }

        $css = ".block-variant-{$variantName} .btn-variant {\n";
        if (isset($btnData['bg'])) {
            $css .= "  background-color: {$btnData['bg']};\n";
        }
        if (isset($btnData['text'])) {
            $css .= "  color: {$btnData['text']};\n";
        }
        if (isset($btnData['radius'])) {
            $css .= "  border-radius: {$this->resolveRadius((string) $btnData['radius'])};\n";
        }
        if (isset($btnData['border']) && 'none' !== $btnData['border']) {
            $css .= "  border: 1px solid {$btnData['border']};\n";
        } else {
            $css .= "  border: none;\n";
        }
        $css .= "  cursor: pointer;\n";
        $css .= "  display: inline-block;\n";
        $css .= "  text-decoration: none;\n";
        $css .= "  transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;\n";
        $css .= "}\n";

        $css .= ".block-variant-{$variantName} .btn-variant:hover {\n";
        if (isset($btnData['hoverBg'])) {
            $css .= "  background-color: {$btnData['hoverBg']};\n";
        }
        if (isset($btnData['hoverText'])) {
            $css .= "  color: {$btnData['hoverText']};\n";
        }
        if (isset($btnData['hoverBorder']) && 'none' !== $btnData['hoverBorder']) {
            $css .= "  border-color: {$btnData['hoverBorder']};\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate CSS for block variant separator (hr) styles.
     *
     * Supports three modes:
     * - "style" (default): Predefined CSS styles (solid, dashed, dotted, double, gradient, wave, zigzag, dots, diamond)
     * - "image": The separator image is rendered via Twig, CSS just hides the default hr
     * - "none": Hides the hr completely
     *
     * @param string               $variantName The variant key
     * @param array<string, mixed> $props       The variant properties
     *
     * @return string CSS rules for the separator
     */
    private function generateSeparatorCss(string $variantName, array $props): string
    {
        $css = '';
        $prefix = ".block-variant-{$variantName}";
        $hrColor = 'var(--variant-hr-color, var(--color-border, #e5e7eb))';
        $mode = $props['separatorMode'] ?? 'style';
        $style = $props['separatorStyle'] ?? 'solid';

        // Hide hr when mode is "none" or "image" (image mode renders via Twig)
        if ('none' === $mode) {
            $css .= "{$prefix} hr,\n{$prefix} .block-separator {\n";
            $css .= "  display: none;\n";
            $css .= "}\n";

            return $css;
        }

        if ('image' === $mode) {
            $css .= "{$prefix} hr {\n";
            $css .= "  display: none;\n";
            $css .= "}\n";

            return $css;
        }

        // Style mode — generate CSS based on selected style
        switch ($style) {
            case 'dashed':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  border-top: 2px dashed {$hrColor};\n";
                $css .= "  background: none;\n";
                $css .= "  height: auto;\n";
                $css .= "}\n";
                break;

            case 'dotted':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  border-top: 2px dotted {$hrColor};\n";
                $css .= "  background: none;\n";
                $css .= "  height: auto;\n";
                $css .= "}\n";
                break;

            case 'double':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  border-top: 3px double {$hrColor};\n";
                $css .= "  background: none;\n";
                $css .= "  height: auto;\n";
                $css .= "}\n";
                break;

            case 'gradient':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  height: 2px;\n";
                $css .= "  background: linear-gradient(to right, transparent, {$hrColor}, transparent);\n";
                $css .= "}\n";
                break;

            case 'wave':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  height: 12px;\n";
                $css .= "  background: none;\n";
                $css .= "  background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 12'%3E%3Cpath d='M0 6 Q12.5 0 25 6 T50 6 T75 6 T100 6' fill='none' stroke='currentColor' stroke-width='2'/%3E%3C/svg%3E\");\n";
                $css .= "  background-size: 100px 12px;\n";
                $css .= "  background-repeat: repeat-x;\n";
                $css .= "  color: {$hrColor};\n";
                $css .= "}\n";
                break;

            case 'zigzag':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  height: 10px;\n";
                $css .= "  background: none;\n";
                $css .= "  background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 10'%3E%3Cpath d='M0 5 L10 0 L20 5 L30 0 L40 5 L30 10 L20 5 L10 10 Z' fill='none' stroke='currentColor' stroke-width='1.5'/%3E%3C/svg%3E\");\n";
                $css .= "  background-size: 40px 10px;\n";
                $css .= "  background-repeat: repeat-x;\n";
                $css .= "  color: {$hrColor};\n";
                $css .= "}\n";
                break;

            case 'dots':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  height: 6px;\n";
                $css .= "  background: none;\n";
                $css .= "  background-image: radial-gradient(circle, {$hrColor} 1.5px, transparent 1.5px);\n";
                $css .= "  background-size: 16px 6px;\n";
                $css .= "  background-repeat: repeat-x;\n";
                $css .= "  background-position: center;\n";
                $css .= "}\n";
                break;

            case 'diamond':
                $css .= "{$prefix} hr {\n";
                $css .= "  border: none;\n";
                $css .= "  height: 10px;\n";
                $css .= "  background: none;\n";
                $css .= "  background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 10'%3E%3Cpath d='M10 0 L15 5 L10 10 L5 5 Z' fill='currentColor'/%3E%3C/svg%3E\");\n";
                $css .= "  background-size: 20px 10px;\n";
                $css .= "  background-repeat: repeat-x;\n";
                $css .= "  color: {$hrColor};\n";
                $css .= "}\n";
                break;

            case 'solid':
            default:
                $css .= "{$prefix} hr {\n";
                $css .= "  background-color: {$hrColor};\n";
                $css .= "}\n";
                break;
        }

        return $css;
    }

    /**
     * Build the absolute file path for a compiled CSS file.
     *
     * @param ThemeConfig $theme The theme configuration
     *
     * @return string The absolute file path
     */
    private function buildFilePath(ThemeConfig $theme): string
    {
        return $this->cssOutputDir . '/' . $this->buildFilename($theme);
    }

    /**
     * Build the filename for a compiled CSS file.
     *
     * Uses the theme ID and a hash of the updatedAt timestamp to enable
     * cache busting when the theme is modified.
     *
     * @param ThemeConfig $theme The theme configuration
     *
     * @return string The filename (e.g. "theme-1-abc123.css")
     */
    private function buildFilename(ThemeConfig $theme): string
    {
        $hash = md5($theme->getUpdatedAt()->format('U.u'));

        return sprintf('theme-%d-%s.css', $theme->getId() ?? 0, substr($hash, 0, 8));
    }

    /**
     * Determine if a background color is perceptually "light".
     *
     * Uses the ITU-R BT.601 luma formula (0.299R + 0.587G + 0.114B).
     * Non-hex values (rgba, named colors) default to light.
     *
     * @param string $color A CSS color value (ideally hex)
     *
     * @return bool True if the color is light (luminance > 0.5)
     */
    private function isLightBackground(string $color): bool
    {
        if (!str_starts_with($color, '#')) {
            return true;
        }

        $hex = ltrim($color, '#');

        if (3 === \strlen($hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (6 !== \strlen($hex)) {
            return true;
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5;
    }

    /**
     * Ensure the CSS output directory exists.
     *
     * @throws \RuntimeException If the directory cannot be created
     */
    private function ensureOutputDir(): void
    {
        if (!is_dir($this->cssOutputDir)) {
            if (!mkdir($this->cssOutputDir, 0775, true) && !is_dir($this->cssOutputDir)) {
                throw new \RuntimeException(sprintf(
                    'Unable to create CSS output directory: %s',
                    $this->cssOutputDir,
                ));
            }
        }
    }
}
