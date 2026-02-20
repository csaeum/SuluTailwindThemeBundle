<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Main bundle class for the SuluThemeBundle.
 *
 * Provides a complete theming system based on design tokens (JSON)
 * compiled to CSS custom properties for Sulu CMS 3.x.
 */
class ItechWorldSuluThemeBundle extends AbstractBundle
{
    /**
     * Define the bundle configuration schema.
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('css_output_dir')
                    ->defaultValue('%kernel.project_dir%/var/cache/iw_sulu_theme')
                    ->info('Directory where compiled theme CSS files are stored')
                ->end()
                ->scalarNode('public_css_path')
                    ->defaultValue('/build/iw-theme')
                    ->info('Public path prefix for serving compiled CSS')
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
                        'ItechWorldSuluThemeBundle' => [
                            'type' => 'attribute',
                            'is_bundle' => false,
                            'dir' => __DIR__ . '/Entity',
                            'prefix' => 'ItechWorld\\SuluThemeBundle\\Entity',
                            'alias' => 'ItechWorldSuluThemeBundle',
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
                            'list' => 'iw_sulu_theme.get_theme_configs',
                            'detail' => 'iw_sulu_theme.get_theme_config',
                        ],
                    ],
                ],
            ]);

            // Register page template directories
            $builder->prependExtensionConfig('sulu_admin', [
                'templates' => [
                    'page' => [
                        'directories' => [
                            'iw_sulu_theme' => __DIR__ . '/../config/templates/pages',
                        ],
                    ],
                ],
            ]);

            // Register global block type directories
            $builder->prependExtensionConfig('sulu_admin', [
                'templates' => [
                    'block' => [
                        'directories' => [
                            'iw_sulu_theme' => __DIR__ . '/../config/templates/blocks',
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
            'itech_world_sulu_theme.css_output_dir',
            $config['css_output_dir'],
        );
        $container->parameters()->set(
            'itech_world_sulu_theme.public_css_path',
            $config['public_css_path'],
        );

        $container->import('../config/services.yaml');
    }
}
