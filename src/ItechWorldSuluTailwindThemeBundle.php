<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Main bundle class for the SuluTailwindThemeBundle.
 *
 * Provides a complete theming system based on design tokens (JSON)
 * compiled to CSS custom properties for Sulu CMS 3.x.
 */
class ItechWorldSuluTailwindThemeBundle extends AbstractBundle
{
    /**
     * Define the bundle configuration schema.
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('google_fonts_api_key')
                    ->defaultNull()
                    ->info('Google Fonts API key (from env: %env(GOOGLE_FONTS_API_KEY)%)')
                ->end()
            ->end();
    }

    /**
     * Prepend configuration into other bundles (sulu_admin, doctrine).
     */
    public function prependExtension(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        // Register Doctrine ORM mapping for this bundle's entities
        if ($builder->hasExtension('doctrine')) {
            $builder->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'ItechWorldSuluTailwindThemeBundle' => [
                            'type' => 'attribute',
                            'is_bundle' => false,
                            'dir' => __DIR__ . '/Entity',
                            'prefix' => 'ItechWorld\\SuluTailwindThemeBundle\\Entity',
                            'alias' => 'ItechWorldSuluTailwindThemeBundle',
                        ],
                    ],
                ],
            ]);
        }

        // Register Sulu admin resources: lists, forms, and API routes
        if ($builder->hasExtension('sulu_admin')) {
            $builder->prependExtensionConfig('sulu_admin', [
                'lists' => [
                    'directories' => [
                        __DIR__ . '/../config/lists',
                    ],
                ],
                'forms' => [
                    'directories' => [
                        __DIR__ . '/../config/forms',
                    ],
                ],
                'resources' => [
                    'iw_theme_configs' => [
                        'routes' => [
                            'list' => 'iw_sulu_tailwind_theme.get_theme_configs',
                            'detail' => 'iw_sulu_tailwind_theme.get_theme_config',
                        ],
                    ],
                ],
            ]);

            // Register page template directories
            $builder->prependExtensionConfig('sulu_admin', [
                'templates' => [
                    'page' => [
                        'directories' => [
                            'iw_sulu_tailwind_theme' => __DIR__ . '/../config/templates/pages',
                        ],
                    ],
                ],
            ]);

            // Register global block type directories
            $builder->prependExtensionConfig('sulu_admin', [
                'templates' => [
                    'block' => [
                        'directories' => [
                            'iw_sulu_tailwind_theme' => __DIR__ . '/../config/templates/blocks',
                        ],
                    ],
                ],
            ]);

            // Register the form block variant based on SuluFormBundle availability.
            // When the bundle is installed, the form block includes a single_form_selection
            // field; otherwise it only offers a Twig template path input.
            $formBlockDir = class_exists(\Sulu\Bundle\FormBundle\SuluFormBundle::class)
                ? __DIR__ . '/../config/templates/blocks-form-bundle'
                : __DIR__ . '/../config/templates/blocks-form';

            $builder->prependExtensionConfig('sulu_admin', [
                'templates' => [
                    'block' => [
                        'directories' => [
                            'iw_sulu_tailwind_theme_form' => $formBlockDir,
                        ],
                    ],
                ],
            ]);

            // Register snippet template directories
            $builder->prependExtensionConfig('sulu_admin', [
                'templates' => [
                    'snippet' => [
                        'directories' => [
                            'iw_sulu_tailwind_theme' => __DIR__ . '/../config/templates/snippets',
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Load bundle services and set configuration parameters.
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->parameters()->set(
            'itech_world_sulu_tailwind_theme.css_output_dir',
            '%kernel.project_dir%/var/cache/iw_sulu_tailwind_theme',
        );
        $container->parameters()->set(
            'itech_world_sulu_tailwind_theme.google_fonts_api_key',
            $config['google_fonts_api_key'],
        );

        $container->import('../config/services.yaml');
    }
}
