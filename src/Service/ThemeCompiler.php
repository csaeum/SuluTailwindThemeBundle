<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Service;

use ItechWorld\SuluThemeBundle\Entity\ThemeConfig;

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
        $css .= $this->generateTypographyVariables($typography);
        $css .= $this->generateBorderVariables($tokens['borders'] ?? []);
        $css .= $this->generateButtonVariables($tokens['buttons'] ?? []);
        $css .= $this->generateMenuVariables($menuConfig);
        $css .= "}\n\n";

        // Button classes
        $css .= $this->generateButtonClasses($tokens['buttons'] ?? []);

        // Block variant classes
        $css .= $this->generateBlockVariantClasses($tokens['blockVariants'] ?? []);

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
     * Generate CSS custom properties for typography tokens.
     *
     * @param array<string, mixed> $typography Typography token values
     *
     * @return string CSS variable declarations
     */
    private function generateTypographyVariables(array $typography): string
    {
        $css = "  /* Typography */\n";

        $baseFontSize = $typography['baseFontSize'] ?? '16px';
        $baseLineHeight = $typography['baseLineHeight'] ?? '1.5';
        $css .= "  --font-size-base: {$baseFontSize};\n";
        $css .= "  --line-height-base: {$baseLineHeight};\n";

        $families = $typography['families'] ?? [];
        foreach ($families as $family) {
            $role = $family['role'] ?? 'body';
            $name = $family['name'] ?? 'sans-serif';
            $fallback = $family['fallback'] ?? 'sans-serif';
            $css .= "  --font-family-{$role}: '{$name}', {$fallback};\n";
        }

        $scale = $typography['scale'] ?? [];
        foreach ($scale as $key => $value) {
            $css .= "  --font-size-{$key}: {$value};\n";
        }

        return $css . "\n";
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
     * Each variant generates a `.block-variant-{name}` class with CSS custom
     * properties for title, subtitle, paragraph, link, list, hr, paragraphBg
     * and blockBg colors. Templates use these properties for consistent styling.
     *
     * @param array<string, mixed> $blockVariants Block variant definitions
     *
     * @return string CSS class declarations
     */
    private function generateBlockVariantClasses(array $blockVariants): string
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

        foreach ($blockVariants as $variantName => $props) {
            if (!is_array($props)) {
                continue;
            }
            $css .= ".block-variant-{$variantName} {\n";

            foreach ($propertyMap as $tokenKey => $cssProperty) {
                if (!isset($props[$tokenKey])) {
                    continue;
                }
                if (str_starts_with($cssProperty, '--')) {
                    $css .= "  {$cssProperty}: {$props[$tokenKey]};\n";
                } else {
                    $css .= "  {$cssProperty}: {$props[$tokenKey]};\n";
                }
            }

            // Apply title color as default text color for the block
            if (isset($props['title'])) {
                $css .= "  color: {$props['title']};\n";
            }

            $css .= "}\n";

            // Child element selectors using custom properties
            $css .= ".block-variant-{$variantName} h1,\n";
            $css .= ".block-variant-{$variantName} h2,\n";
            $css .= ".block-variant-{$variantName} h3,\n";
            $css .= ".block-variant-{$variantName} h4,\n";
            $css .= ".block-variant-{$variantName} h5,\n";
            $css .= ".block-variant-{$variantName} h6 {\n";
            $css .= "  color: var(--variant-title-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$variantName} .block-subtitle {\n";
            $css .= "  color: var(--variant-subtitle-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$variantName} p {\n";
            $css .= "  color: var(--variant-paragraph-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$variantName} a {\n";
            $css .= "  color: var(--variant-link-color, inherit);\n";
            $css .= "}\n";

            $css .= ".block-variant-{$variantName} a:hover {\n";
            $css .= "  color: var(--variant-link-hover, var(--variant-link-color, inherit));\n";
            $css .= "}\n";

            $css .= ".block-variant-{$variantName} ul,\n";
            $css .= ".block-variant-{$variantName} ol {\n";
            $css .= "  color: var(--variant-list-color, inherit);\n";
            $css .= "}\n";

            $css .= $this->generateSeparatorCss($variantName, $props);

            // Apply paragraph background only when the variant defines it
            // When a background is set, add margin so the bg doesn't touch block edges
            if (isset($props['paragraphBg'])) {
                $css .= ".block-variant-{$variantName} .block-text {\n";
                $css .= "  background-color: var(--variant-paragraph-bg);\n";
                $css .= "  padding: 1rem 1.5rem;\n";
                $css .= "  margin: 1rem;\n";
                $css .= "}\n";
            }

            $css .= "\n";
        }

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
