<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\DataFixtures;

/**
 * Provides preset theme data for the 6 built-in themes.
 *
 * Each preset contains complete design tokens (colors, typography, borders,
 * buttons, block variants), menu configuration, and block styles for all
 * 8 block types.
 *
 * Button property names match the admin form fields:
 *   bg, text, border, hoverBg, hoverText, hoverBorder, radius
 *
 * Border/button radius values use Tailwind class names (e.g. "rounded-lg")
 * which are resolved to CSS values by the ThemeCompiler.
 *
 * Typography families use internal names:
 *   name, role, source, weights (array), fallback
 *
 * Block variants use indexed arrays (position 0, 1, 2) with properties:
 *   label, title, subtitle, paragraph, link, list, hr, paragraphBg, blockBg
 */
class ThemeFixtures
{
    /**
     * Get all available theme presets.
     *
     * @return array<string, array<string, mixed>> Keyed by theme name
     */
    public static function getPresets(): array
    {
        return [
            'corporate' => self::getCorporatePreset(),
            'creative' => self::getCreativePreset(),
            'minimal' => self::getMinimalPreset(),
            'nature' => self::getNaturePreset(),
            'halloween' => self::getHalloweenPreset(),
            'christmas' => self::getChristmasPreset(),
            'megamenu' => self::getMegamenuPreset(),
        ];
    }

    /**
     * Corporate theme: professional blues, Inter font, rounded-lg radius.
     *
     * @return array<string, mixed>
     */
    private static function getCorporatePreset(): array
    {
        return [
            'name' => 'corporate',
            'label' => 'Corporate Professional',
            'tokens' => [
                'colors' => [
                    'primary' => '#1a56db',
                    'secondary' => '#475569',
                    'accent' => '#F59E0B',
                    'background' => '#ffffff',
                    'text' => '#1e293b',
                    'link' => '#1a56db',
                    'linkHover' => '#1e40af',
                ],
                'typography' => [
                    'baseFontSize' => '16px',
                    'baseLineHeight' => '1.6',
                    'families' => [
                        [
                            'name' => 'Inter',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [500, 600, 700],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                        [
                            'name' => 'Inter',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [400, 500],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '1.875rem',
                        '4xl' => '2.25rem',
                        '5xl' => '3rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '700'],
                        'h2' => ['family' => 'heading', 'weight' => '600'],
                        'h3' => ['family' => 'heading', 'weight' => '600'],
                        'h4' => ['family' => 'heading', 'weight' => '500'],
                        'h5' => ['family' => 'heading', 'weight' => '500'],
                        'h6' => ['family' => 'heading', 'weight' => '500'],
                        'body' => ['family' => 'body', 'weight' => '400'],
                        'link' => ['family' => 'body', 'weight' => '500'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-lg',
                    'radiusSm' => 'rounded-sm',
                    'radiusLg' => 'rounded-xl',
                    'radiusFull' => 'rounded-full',
                    'imageRadius' => 'rounded-lg',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#1a56db',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#1e40af',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-lg',
                    ],
                    'secondary' => [
                        'bg' => '#ffffff',
                        'text' => '#1a56db',
                        'border' => '#1a56db',
                        'hoverBg' => '#f8fafc',
                        'hoverText' => '#1e40af',
                        'hoverBorder' => '#1e40af',
                        'radius' => 'rounded-lg',
                    ],
                    'accent' => [
                        'bg' => '#f59e0b',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#d97706',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-lg',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Clair',
                        'title' => '#1e293b',
                        'subtitle' => '#475569',
                        'paragraph' => '#475569',
                        'link' => '#1a56db',
                        'list' => '#475569',
                        'hr' => '#e2e8f0',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#ffffff',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Accent primaire',
                        'title' => '#ffffff',
                        'subtitle' => '#dbeafe',
                        'paragraph' => '#dbeafe',
                        'link' => '#ffffff',
                        'list' => '#dbeafe',
                        'hr' => 'rgba(255,255,255,0.2)',
                        'paragraphBg' => 'rgba(255,255,255,0.08)',
                        'blockBg' => '#1a56db',
                        'buttonStyle' => 'secondary',
                    ],
                    [
                        'label' => 'Sombre',
                        'title' => '#f8fafc',
                        'subtitle' => '#cbd5e1',
                        'paragraph' => '#cbd5e1',
                        'link' => '#60a5fa',
                        'list' => '#cbd5e1',
                        'hr' => '#334155',
                        'paragraphBg' => '#1e293b',
                        'blockBg' => '#0f172a',
                        'buttonStyle' => 'accent',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'navbar',
                'animation' => 'none',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => true,
                'childLevels' => 3,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => false,
                'displaySocialMedia' => false,
                'colors' => [
                    'bg' => '#ffffff',
                    'text' => '#1e293b',
                    'textHover' => '#1a56db',
                    'secondBg' => '#f8fafc',
                    'secondText' => '#475569',
                    'secondTextHover' => '#1a56db',
                    'thirdBg' => '#f1f5f9',
                    'thirdText' => '#64748b',
                    'divider' => '#e2e8f0',
                    'burgerOpen' => '#1e293b',
                    'burgerClose' => '#ffffff',
                    'socialMedia' => '#94a3b8',
                    'socialMediaHover' => '#1a56db',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }

    /**
     * Creative theme: pink + amber, Poppins/DM Sans, rounded-2xl radius, bold.
     *
     * @return array<string, mixed>
     */
    private static function getCreativePreset(): array
    {
        return [
            'name' => 'creative',
            'label' => 'Creative Bold',
            'tokens' => [
                'colors' => [
                    'primary' => '#ec4899',
                    'secondary' => '#f59e0b',
                    'accent' => '#EC4899',
                    'background' => '#fffbeb',
                    'text' => '#1f2937',
                    'link' => '#ec4899',
                    'linkHover' => '#db2777',
                ],
                'typography' => [
                    'baseFontSize' => '16px',
                    'baseLineHeight' => '1.7',
                    'families' => [
                        [
                            'name' => 'Poppins',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [600, 700, 800],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                        [
                            'name' => 'DM Sans',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [400, 500],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '2rem',
                        '4xl' => '2.5rem',
                        '5xl' => '3.5rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '800'],
                        'h2' => ['family' => 'heading', 'weight' => '700'],
                        'h3' => ['family' => 'heading', 'weight' => '700'],
                        'h4' => ['family' => 'heading', 'weight' => '600'],
                        'h5' => ['family' => 'heading', 'weight' => '600'],
                        'h6' => ['family' => 'heading', 'weight' => '600'],
                        'body' => ['family' => 'body', 'weight' => '400'],
                        'link' => ['family' => 'body', 'weight' => '500'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-2xl',
                    'radiusSm' => 'rounded-lg',
                    'radiusLg' => 'rounded-3xl',
                    'radiusFull' => 'rounded-full',
                    'imageRadius' => 'rounded-2xl',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#ec4899',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#db2777',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-2xl',
                    ],
                    'secondary' => [
                        'bg' => '#f59e0b',
                        'text' => '#1f2937',
                        'border' => 'none',
                        'hoverBg' => '#d97706',
                        'hoverText' => '#1f2937',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-2xl',
                    ],
                    'accent' => [
                        'bg' => '#8b5cf6',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#7c3aed',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-2xl',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Clair',
                        'title' => '#1f2937',
                        'subtitle' => '#6b7280',
                        'paragraph' => '#6b7280',
                        'link' => '#ec4899',
                        'list' => '#6b7280',
                        'hr' => '#fde68a',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#fffbeb',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Accent rose',
                        'title' => '#ffffff',
                        'subtitle' => '#fce7f3',
                        'paragraph' => '#fce7f3',
                        'link' => '#fbbf24',
                        'list' => '#fce7f3',
                        'hr' => 'rgba(255,255,255,0.2)',
                        'paragraphBg' => 'rgba(255,255,255,0.08)',
                        'blockBg' => '#ec4899',
                        'buttonStyle' => 'secondary',
                    ],
                    [
                        'label' => 'Sombre',
                        'title' => '#fdf2f8',
                        'subtitle' => '#d1d5db',
                        'paragraph' => '#d1d5db',
                        'link' => '#f472b6',
                        'list' => '#d1d5db',
                        'hr' => '#374151',
                        'paragraphBg' => '#1f2937',
                        'blockBg' => '#111827',
                        'buttonStyle' => 'accent',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'navbar',
                'animation' => 'fade',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => false,
                'childLevels' => 2,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => true,
                'displaySocialMedia' => true,
                'colors' => [
                    'bg' => '#ffffff',
                    'text' => '#1f2937',
                    'textHover' => '#ec4899',
                    'secondBg' => '#fdf2f8',
                    'secondText' => '#6b7280',
                    'secondTextHover' => '#ec4899',
                    'thirdBg' => '#fce7f3',
                    'thirdText' => '#9ca3af',
                    'divider' => '#fde68a',
                    'burgerOpen' => '#1f2937',
                    'burgerClose' => '#ffffff',
                    'socialMedia' => '#9ca3af',
                    'socialMediaHover' => '#ec4899',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }

    /**
     * Minimal theme: black/white, Playfair Display/Inter, no radius, clean.
     *
     * @return array<string, mixed>
     */
    private static function getMinimalPreset(): array
    {
        return [
            'name' => 'minimal',
            'label' => 'Minimal Clean',
            'tokens' => [
                'colors' => [
                    'primary' => '#000000',
                    'secondary' => '#6b7280',
                    'accent' => '#6366F1',
                    'background' => '#ffffff',
                    'text' => '#111111',
                    'link' => '#000000',
                    'linkHover' => '#374151',
                ],
                'typography' => [
                    'baseFontSize' => '18px',
                    'baseLineHeight' => '1.8',
                    'families' => [
                        [
                            'name' => 'Playfair Display',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [400, 700],
                            'fallback' => 'Georgia, serif',
                        ],
                        [
                            'name' => 'Inter',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [300, 400],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '1.875rem',
                        '4xl' => '2.25rem',
                        '5xl' => '3rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '700'],
                        'h2' => ['family' => 'heading', 'weight' => '700'],
                        'h3' => ['family' => 'heading', 'weight' => '400'],
                        'h4' => ['family' => 'heading', 'weight' => '400'],
                        'h5' => ['family' => 'body', 'weight' => '400'],
                        'h6' => ['family' => 'body', 'weight' => '400'],
                        'body' => ['family' => 'body', 'weight' => '300'],
                        'link' => ['family' => 'body', 'weight' => '400'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-none',
                    'radiusSm' => 'rounded-none',
                    'radiusLg' => 'rounded-none',
                    'radiusFull' => 'rounded-full',
                    'imageRadius' => 'rounded-none',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#000000',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#374151',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-none',
                    ],
                    'secondary' => [
                        'bg' => '#ffffff',
                        'text' => '#000000',
                        'border' => '#000000',
                        'hoverBg' => '#f5f5f5',
                        'hoverText' => '#000000',
                        'hoverBorder' => '#000000',
                        'radius' => 'rounded-none',
                    ],
                    'accent' => [
                        'bg' => '#737373',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#525252',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-none',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Clair',
                        'title' => '#111111',
                        'subtitle' => '#737373',
                        'paragraph' => '#737373',
                        'link' => '#000000',
                        'list' => '#737373',
                        'hr' => '#e5e5e5',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#ffffff',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Gris neutre',
                        'title' => '#111111',
                        'subtitle' => '#525252',
                        'paragraph' => '#525252',
                        'link' => '#000000',
                        'list' => '#525252',
                        'hr' => '#d4d4d4',
                        'paragraphBg' => '#f5f5f5',
                        'blockBg' => '#fafafa',
                        'buttonStyle' => 'secondary',
                    ],
                    [
                        'label' => 'Sombre',
                        'title' => '#fafafa',
                        'subtitle' => '#a3a3a3',
                        'paragraph' => '#a3a3a3',
                        'link' => '#e5e5e5',
                        'list' => '#a3a3a3',
                        'hr' => '#404040',
                        'paragraphBg' => '#171717',
                        'blockBg' => '#111111',
                        'buttonStyle' => 'accent',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'navbar',
                'animation' => 'none',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => true,
                'childLevels' => 2,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => false,
                'displaySocialMedia' => false,
                'colors' => [
                    'bg' => '#ffffff',
                    'text' => '#111111',
                    'textHover' => '#000000',
                    'secondBg' => '#fafafa',
                    'secondText' => '#737373',
                    'secondTextHover' => '#000000',
                    'thirdBg' => '#f5f5f5',
                    'thirdText' => '#a3a3a3',
                    'divider' => '#e5e5e5',
                    'burgerOpen' => '#111111',
                    'burgerClose' => '#ffffff',
                    'socialMedia' => '#a3a3a3',
                    'socialMediaHover' => '#000000',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }

    /**
     * Nature theme: forest green + gold, DM Sans/Open Sans, rounded-xl radius.
     *
     * @return array<string, mixed>
     */
    private static function getNaturePreset(): array
    {
        return [
            'name' => 'nature',
            'label' => 'Nature Organic',
            'tokens' => [
                'colors' => [
                    'primary' => '#065f46',
                    'secondary' => '#d97706',
                    'accent' => '#F97316',
                    'background' => '#fefdf8',
                    'text' => '#1a2e1a',
                    'link' => '#065f46',
                    'linkHover' => '#059669',
                ],
                'typography' => [
                    'baseFontSize' => '16px',
                    'baseLineHeight' => '1.7',
                    'families' => [
                        [
                            'name' => 'DM Sans',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [500, 600, 700],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                        [
                            'name' => 'Open Sans',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [400, 500],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '1.875rem',
                        '4xl' => '2.25rem',
                        '5xl' => '3rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '700'],
                        'h2' => ['family' => 'heading', 'weight' => '600'],
                        'h3' => ['family' => 'heading', 'weight' => '600'],
                        'h4' => ['family' => 'heading', 'weight' => '500'],
                        'h5' => ['family' => 'heading', 'weight' => '500'],
                        'h6' => ['family' => 'heading', 'weight' => '500'],
                        'body' => ['family' => 'body', 'weight' => '400'],
                        'link' => ['family' => 'body', 'weight' => '500'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-xl',
                    'radiusSm' => 'rounded-md',
                    'radiusLg' => 'rounded-2xl',
                    'radiusFull' => 'rounded-full',
                    'imageRadius' => 'rounded-xl',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#065f46',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#064e3b',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                    'secondary' => [
                        'bg' => '#d97706',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#b45309',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                    'accent' => [
                        'bg' => '#059669',
                        'text' => '#ffffff',
                        'border' => '#047857',
                        'hoverBg' => '#047857',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => '#065f46',
                        'radius' => 'rounded-xl',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Clair nature',
                        'title' => '#1a2e1a',
                        'subtitle' => '#4d7c4d',
                        'paragraph' => '#4d7c4d',
                        'link' => '#065f46',
                        'list' => '#4d7c4d',
                        'hr' => '#d1e7dd',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#fefdf8',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Accent vert',
                        'title' => '#ffffff',
                        'subtitle' => '#a7f3d0',
                        'paragraph' => '#a7f3d0',
                        'link' => '#fbbf24',
                        'list' => '#a7f3d0',
                        'hr' => 'rgba(255,255,255,0.2)',
                        'paragraphBg' => 'rgba(255,255,255,0.08)',
                        'blockBg' => '#065f46',
                        'buttonStyle' => 'secondary',
                    ],
                    [
                        'label' => 'Sombre foret',
                        'title' => '#ecfdf5',
                        'subtitle' => '#86efac',
                        'paragraph' => '#86efac',
                        'link' => '#34d399',
                        'list' => '#86efac',
                        'hr' => '#064e3b',
                        'paragraphBg' => '#022c22',
                        'blockBg' => '#064e3b',
                        'buttonStyle' => 'accent',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'navbar',
                'animation' => 'slide',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => true,
                'childLevels' => 3,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => true,
                'displaySocialMedia' => true,
                'colors' => [
                    'bg' => '#fefdf8',
                    'text' => '#1a2e1a',
                    'textHover' => '#065f46',
                    'secondBg' => '#ecfdf5',
                    'secondText' => '#4d7c4d',
                    'secondTextHover' => '#065f46',
                    'thirdBg' => '#d1fae5',
                    'thirdText' => '#6b7280',
                    'divider' => '#d1e7dd',
                    'burgerOpen' => '#1a2e1a',
                    'burgerClose' => '#ffffff',
                    'socialMedia' => '#4d7c4d',
                    'socialMediaHover' => '#065f46',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }

    /**
     * Halloween theme: orange/purple/black, Creepster/Nunito Sans, rounded-xl radius, spooky.
     *
     * @return array<string, mixed>
     */
    private static function getHalloweenPreset(): array
    {
        return [
            'name' => 'halloween',
            'label' => 'Halloween Spooky',
            'tokens' => [
                'colors' => [
                    'primary' => '#f97316',
                    'secondary' => '#7c3aed',
                    'accent' => '#eab308',
                    'background' => '#1c1917',
                    'text' => '#fafaf9',
                    'link' => '#f97316',
                    'linkHover' => '#fb923c',
                ],
                'typography' => [
                    'baseFontSize' => '16px',
                    'baseLineHeight' => '1.6',
                    'families' => [
                        [
                            'name' => 'Creepster',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [400],
                            'fallback' => 'cursive, system-ui',
                        ],
                        [
                            'name' => 'Nunito Sans',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [400, 600],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '2rem',
                        '4xl' => '2.75rem',
                        '5xl' => '3.5rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '400'],
                        'h2' => ['family' => 'heading', 'weight' => '400'],
                        'h3' => ['family' => 'heading', 'weight' => '400'],
                        'h4' => ['family' => 'heading', 'weight' => '400'],
                        'h5' => ['family' => 'body', 'weight' => '600'],
                        'h6' => ['family' => 'body', 'weight' => '600'],
                        'body' => ['family' => 'body', 'weight' => '400'],
                        'link' => ['family' => 'body', 'weight' => '600'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-xl',
                    'radiusSm' => 'rounded-md',
                    'radiusLg' => 'rounded-2xl',
                    'radiusFull' => 'rounded-full',
                    'imageRadius' => 'rounded-xl',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#f97316',
                        'text' => '#1c1917',
                        'border' => 'none',
                        'hoverBg' => '#ea580c',
                        'hoverText' => '#1c1917',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                    'secondary' => [
                        'bg' => '#7c3aed',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#6d28d9',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                    'accent' => [
                        'bg' => '#eab308',
                        'text' => '#1c1917',
                        'border' => 'none',
                        'hoverBg' => '#ca8a04',
                        'hoverText' => '#1c1917',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Nuit noire',
                        'title' => '#f97316',
                        'subtitle' => '#a8a29e',
                        'paragraph' => '#d6d3d1',
                        'link' => '#f97316',
                        'list' => '#d6d3d1',
                        'hr' => '#44403c',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#1c1917',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Citrouille',
                        'title' => '#1c1917',
                        'subtitle' => '#431407',
                        'paragraph' => '#431407',
                        'link' => '#7c3aed',
                        'list' => '#431407',
                        'hr' => 'rgba(0,0,0,0.15)',
                        'paragraphBg' => 'rgba(0,0,0,0.05)',
                        'blockBg' => '#f97316',
                        'buttonStyle' => 'secondary',
                    ],
                    [
                        'label' => 'Violet sorcier',
                        'title' => '#ffffff',
                        'subtitle' => '#c4b5fd',
                        'paragraph' => '#ddd6fe',
                        'link' => '#f97316',
                        'list' => '#ddd6fe',
                        'hr' => 'rgba(255,255,255,0.15)',
                        'paragraphBg' => 'rgba(255,255,255,0.06)',
                        'blockBg' => '#5b21b6',
                        'buttonStyle' => 'accent',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'navbar',
                'animation' => 'fade',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => true,
                'childLevels' => 2,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => true,
                'displaySocialMedia' => false,
                'colors' => [
                    'bg' => '#1c1917',
                    'text' => '#fafaf9',
                    'textHover' => '#f97316',
                    'secondBg' => '#292524',
                    'secondText' => '#a8a29e',
                    'secondTextHover' => '#f97316',
                    'thirdBg' => '#44403c',
                    'thirdText' => '#d6d3d1',
                    'divider' => '#44403c',
                    'burgerOpen' => '#f97316',
                    'burgerClose' => '#f97316',
                    'socialMedia' => '#a8a29e',
                    'socialMediaHover' => '#f97316',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }

    /**
     * Christmas theme: red/green/gold, Mountains of Christmas/Source Sans 3, rounded-xl radius, festive.
     *
     * @return array<string, mixed>
     */
    private static function getChristmasPreset(): array
    {
        return [
            'name' => 'christmas',
            'label' => 'Christmas Festive',
            'tokens' => [
                'colors' => [
                    'primary' => '#dc2626',
                    'secondary' => '#15803d',
                    'accent' => '#d97706',
                    'background' => '#fef2f2',
                    'text' => '#1c1917',
                    'link' => '#dc2626',
                    'linkHover' => '#b91c1c',
                ],
                'typography' => [
                    'baseFontSize' => '16px',
                    'baseLineHeight' => '1.7',
                    'families' => [
                        [
                            'name' => 'Mountains of Christmas',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [400, 700],
                            'fallback' => 'cursive, Georgia, serif',
                        ],
                        [
                            'name' => 'Source Sans 3',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [400, 600],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '2rem',
                        '4xl' => '2.5rem',
                        '5xl' => '3.25rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '700'],
                        'h2' => ['family' => 'heading', 'weight' => '700'],
                        'h3' => ['family' => 'heading', 'weight' => '400'],
                        'h4' => ['family' => 'heading', 'weight' => '400'],
                        'h5' => ['family' => 'body', 'weight' => '600'],
                        'h6' => ['family' => 'body', 'weight' => '600'],
                        'body' => ['family' => 'body', 'weight' => '400'],
                        'link' => ['family' => 'body', 'weight' => '600'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-xl',
                    'radiusSm' => 'rounded-md',
                    'radiusLg' => 'rounded-xl',
                    'radiusFull' => 'rounded-full',
                    'imageRadius' => 'rounded-xl',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#dc2626',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#b91c1c',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                    'secondary' => [
                        'bg' => '#15803d',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#166534',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                    'accent' => [
                        'bg' => '#d97706',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#b45309',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-xl',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Neige',
                        'title' => '#991b1b',
                        'subtitle' => '#57534e',
                        'paragraph' => '#44403c',
                        'link' => '#dc2626',
                        'list' => '#44403c',
                        'hr' => '#fecaca',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#fef2f2',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Sapin',
                        'title' => '#ffffff',
                        'subtitle' => '#bbf7d0',
                        'paragraph' => '#dcfce7',
                        'link' => '#fbbf24',
                        'list' => '#dcfce7',
                        'hr' => 'rgba(255,255,255,0.2)',
                        'paragraphBg' => 'rgba(255,255,255,0.08)',
                        'blockBg' => '#15803d',
                        'buttonStyle' => 'accent',
                    ],
                    [
                        'label' => 'Rouge festif',
                        'title' => '#ffffff',
                        'subtitle' => '#fecaca',
                        'paragraph' => '#fee2e2',
                        'link' => '#fbbf24',
                        'list' => '#fee2e2',
                        'hr' => 'rgba(255,255,255,0.2)',
                        'paragraphBg' => 'rgba(255,255,255,0.06)',
                        'blockBg' => '#dc2626',
                        'buttonStyle' => 'secondary',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'navbar',
                'animation' => 'slide',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => true,
                'childLevels' => 3,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => true,
                'displaySocialMedia' => true,
                'colors' => [
                    'bg' => '#fef2f2',
                    'text' => '#1c1917',
                    'textHover' => '#dc2626',
                    'secondBg' => '#fee2e2',
                    'secondText' => '#57534e',
                    'secondTextHover' => '#dc2626',
                    'thirdBg' => '#fecaca',
                    'thirdText' => '#78716c',
                    'divider' => '#fecaca',
                    'burgerOpen' => '#dc2626',
                    'burgerClose' => '#ffffff',
                    'socialMedia' => '#dc2626',
                    'socialMediaHover' => '#15803d',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }

    /**
     * Get default block styles for all 8 block types.
     *
     * Each block type has a default template and multiple style options
     * with their own templates.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function getDefaultBlockStyles(): array
    {
        return [
            'text' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'centered', 'label' => 'Centre', 'twig' => '_style_centered.html.twig', 'default' => true],
                    ['key' => 'left_aligned', 'label' => 'Aligne gauche', 'twig' => '_style_left_aligned.html.twig'],
                    ['key' => 'two_columns', 'label' => 'Deux colonnes', 'twig' => '_style_two_columns.html.twig'],
                ],
            ],
            'text_images' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'classic', 'label' => 'Classique', 'twig' => '_style_classic.html.twig', 'default' => true],
                    ['key' => 'overlay', 'label' => 'Superpose', 'twig' => '_style_overlay.html.twig'],
                    ['key' => 'fullwidth', 'label' => 'Pleine largeur', 'twig' => '_style_fullwidth.html.twig'],
                    ['key' => 'mosaic', 'label' => 'Mosaique', 'twig' => '_style_mosaic.html.twig'],
                    ['key' => 'sidebar', 'label' => 'Bande laterale', 'twig' => '_style_sidebar.html.twig'],
                ],
            ],
            'gallery' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'grid', 'label' => 'Grille', 'twig' => '_style_grid.html.twig', 'default' => true],
                    ['key' => 'masonry', 'label' => 'Masonry', 'twig' => '_style_masonry.html.twig'],
                    ['key' => 'slider', 'label' => 'Slider', 'twig' => '_style_slider.html.twig'],
                    ['key' => 'carousel', 'label' => 'Carrousel', 'twig' => '_style_carousel.html.twig'],
                    ['key' => 'fullscreen_slider', 'label' => 'Slider plein ecran', 'twig' => '_style_fullscreen_slider.html.twig'],
                ],
            ],
            'key_figures' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'inline', 'label' => 'En ligne', 'twig' => '_style_inline.html.twig', 'default' => true],
                    ['key' => 'with_icons', 'label' => 'Avec icones', 'twig' => '_style_with_icons.html.twig'],
                    ['key' => 'grid_2x2', 'label' => 'Grille 2x2', 'twig' => '_style_grid_2x2.html.twig'],
                ],
            ],
            'linked_pages' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'cards', 'label' => 'Cartes', 'twig' => '_style_cards.html.twig', 'default' => true],
                    ['key' => 'list', 'label' => 'Liste', 'twig' => '_style_list.html.twig'],
                    ['key' => 'minimal', 'label' => 'Minimal', 'twig' => '_style_minimal.html.twig'],
                ],
            ],
            'location' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'map_only', 'label' => 'Carte seule', 'twig' => '_style_map_only.html.twig', 'default' => true],
                    ['key' => 'map_with_info', 'label' => 'Carte + infos', 'twig' => '_style_map_with_info.html.twig'],
                ],
            ],
            'form' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'centered', 'label' => 'Centre', 'twig' => '_style_centered.html.twig', 'default' => true],
                    ['key' => 'split', 'label' => 'Avec image', 'twig' => '_style_split.html.twig'],
                ],
            ],
            'document' => [
                'enabled' => true,
                'styles' => [
                    ['key' => 'default', 'label' => 'Par defaut', 'twig' => '_style_default.html.twig', 'default' => true],
                ],
            ],
        ];
    }

    /**
     * Mega menu theme: professional dark navbar with mega dropdown navigation.
     *
     * Uses the megamenu type in native mode (page tree) by default.
     *
     * @return array<string, mixed>
     */
    private static function getMegamenuPreset(): array
    {
        return [
            'name' => 'megamenu',
            'label' => 'Mega Menu Professional',
            'tokens' => [
                'colors' => [
                    'primary' => '#6366f1',
                    'secondary' => '#475569',
                    'accent' => '#f59e0b',
                    'background' => '#ffffff',
                    'text' => '#1e293b',
                    'link' => '#6366f1',
                    'linkHover' => '#4f46e5',
                ],
                'typography' => [
                    'baseFontSize' => '16px',
                    'baseLineHeight' => '1.6',
                    'families' => [
                        [
                            'name' => 'Inter',
                            'role' => 'heading',
                            'source' => 'google',
                            'weights' => [500, 600, 700],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                        [
                            'name' => 'Inter',
                            'role' => 'body',
                            'source' => 'google',
                            'weights' => [400, 500],
                            'fallback' => 'system-ui, sans-serif',
                        ],
                    ],
                    'scale' => [
                        'xs' => '0.75rem',
                        'sm' => '0.875rem',
                        'base' => '1rem',
                        'lg' => '1.125rem',
                        'xl' => '1.25rem',
                        '2xl' => '1.5rem',
                        '3xl' => '1.875rem',
                        '4xl' => '2.25rem',
                        '5xl' => '3rem',
                    ],
                    'assignments' => [
                        'h1' => ['family' => 'heading', 'weight' => '700'],
                        'h2' => ['family' => 'heading', 'weight' => '600'],
                        'h3' => ['family' => 'heading', 'weight' => '600'],
                        'h4' => ['family' => 'heading', 'weight' => '500'],
                        'h5' => ['family' => 'heading', 'weight' => '500'],
                        'h6' => ['family' => 'heading', 'weight' => '500'],
                        'body' => ['family' => 'body', 'weight' => '400'],
                        'link' => ['family' => 'body', 'weight' => '500'],
                    ],
                ],
                'borders' => [
                    'radius' => 'rounded-lg',
                    'imageRadius' => 'rounded-lg',
                ],
                'buttons' => [
                    'primary' => [
                        'bg' => '#6366f1',
                        'text' => '#ffffff',
                        'border' => 'none',
                        'hoverBg' => '#4f46e5',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-lg',
                    ],
                    'secondary' => [
                        'bg' => 'transparent',
                        'text' => '#6366f1',
                        'border' => '#6366f1',
                        'hoverBg' => '#6366f1',
                        'hoverText' => '#ffffff',
                        'hoverBorder' => '#6366f1',
                        'radius' => 'rounded-lg',
                    ],
                    'accent' => [
                        'bg' => '#f59e0b',
                        'text' => '#1e293b',
                        'border' => 'none',
                        'hoverBg' => '#d97706',
                        'hoverText' => '#1e293b',
                        'hoverBorder' => 'none',
                        'radius' => 'rounded-lg',
                    ],
                ],
                'blockVariants' => [
                    [
                        'label' => 'Light',
                        'title' => '#1e293b',
                        'subtitle' => '#475569',
                        'paragraph' => '#334155',
                        'link' => '#6366f1',
                        'linkHover' => '#4f46e5',
                        'list' => '#334155',
                        'hr' => '#e2e8f0',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#ffffff',
                        'buttonStyle' => 'primary',
                    ],
                    [
                        'label' => 'Accent',
                        'title' => '#ffffff',
                        'subtitle' => '#e0e7ff',
                        'paragraph' => '#c7d2fe',
                        'link' => '#fbbf24',
                        'linkHover' => '#f59e0b',
                        'list' => '#c7d2fe',
                        'hr' => 'rgba(255,255,255,0.2)',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#6366f1',
                        'buttonStyle' => 'accent',
                    ],
                    [
                        'label' => 'Dark',
                        'title' => '#f8fafc',
                        'subtitle' => '#94a3b8',
                        'paragraph' => '#cbd5e1',
                        'link' => '#818cf8',
                        'linkHover' => '#a5b4fc',
                        'list' => '#cbd5e1',
                        'hr' => 'rgba(255,255,255,0.1)',
                        'paragraphBg' => 'transparent',
                        'blockBg' => '#0f172a',
                        'buttonStyle' => 'accent',
                    ],
                ],
            ],
            'menuConfig' => [
                'type' => 'megamenu',
                'megamenuSource' => 'native',
                'navPosition' => 'center',
                'animation' => 'none',
                'clickParentPage' => 'none',
                'clickParentPageNavbar' => false,
                'childLevels' => 3,
                'displayLogoDesktop' => true,
                'displayLogoMobile' => true,
                'displaySiteName' => false,
                'displaySocialMedia' => false,
                'transparentNavbar' => false,
                'colors' => [
                    'bg' => '#0f172a',
                    'text' => '#e2e8f0',
                    'textHover' => '#ffffff',
                    'secondBg' => '#1e293b',
                    'secondText' => '#cbd5e1',
                    'secondTextHover' => '#ffffff',
                    'thirdBg' => '#334155',
                    'thirdText' => '#94a3b8',
                    'divider' => 'rgba(255,255,255,0.1)',
                    'burgerOpen' => '#e2e8f0',
                    'burgerClose' => '#e2e8f0',
                    'socialMedia' => '#94a3b8',
                    'socialMediaHover' => '#ffffff',
                ],
            ],
            'blockStyles' => self::getDefaultBlockStyles(),
        ];
    }
}
