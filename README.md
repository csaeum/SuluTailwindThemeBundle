<h1 align="center">SuluThemeBundle</h1>
<h3 align="center">Complete theming system for <a href="https://sulu.io" target="_blank">Sulu CMS 3.x</a></h3>

<p align="center">
    <a href="LICENSE" target="_blank">
        <img src="https://img.shields.io/badge/license-MIT-green" alt="GitHub license">
    </a>
    <a href="https://sulu.io/" target="_blank">
        <img src="https://img.shields.io/badge/sulu-%3E=3.0-cyan" alt="Sulu compatibility">
    </a>
    <a href="https://symfony.com/" target="_blank">
        <img src="https://img.shields.io/badge/symfony-%3E=7.0-blue" alt="Symfony compatibility">
    </a>
</p>

<p align="center">
    A design-token-based theming system that compiles JSON configuration into CSS custom properties.<br>
    Manage colors, typography, buttons, borders, block variants, and menu styles from the Sulu admin interface.
</p>

---

## Requirements

* PHP >= 8.2
* Sulu CMS >= 3.0
* Symfony >= 7.0
* Doctrine ORM >= 3.0

## Features

* **Design tokens**: Store all theme settings as structured JSON, compiled to CSS custom properties
* **Admin interface**: Full CRUD with 7 tabs (details, colors, typography, buttons, borders, block variants, menu)
* **Multiple themes**: Create and switch between theme presets (corporate, creative, minimal, nature)
* **CSS compilation**: Automatic generation of `:root` variables, `.block-variant-*` classes, `.btn-*` styles
* **Google Fonts**: Automatic resolution and inclusion of Google Fonts from typography settings
* **Block variants**: Per-block color schemes (light, accent, dark) applied via CSS custom properties
* **Menu configuration**: Configurable menu type, colors, animation, and display options
* **Twig integration**: Helper functions for including theme CSS, fonts, block styles, and menu config
* **CLI commands**: Install preset themes and recompile CSS from the command line
* **Auto-recompile**: Doctrine listener recompiles CSS on theme save

## Installation

### 1. Require the bundle

```bash
composer require itech-world/sulu-theme-bundle
```

For local development with a path repository, add to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../SuluThemeBundle"
        }
    ],
    "require": {
        "itech-world/sulu-theme-bundle": "dev-dev"
    }
}
```

### 2. Register the bundle

If Symfony Flex doesn't register it automatically, add to `config/bundles.php`:

```php
return [
    // ...
    ItechWorld\SuluThemeBundle\ItechWorldSuluThemeBundle::class => ['all' => true],
];
```

### 3. Register routes

Add the following to your `config/routes.yaml`:

```yaml
itech_world_sulu_theme:
    resource: '@ItechWorldSuluThemeBundle/src/Controller/'
    type: attribute
```

### 4. Update the database schema

```bash
php bin/adminconsole doctrine:schema:update --force
```

### 5. Install a preset theme (optional)

```bash
php bin/adminconsole iw-sulu:theme:install corporate
```

Available presets: `corporate`, `creative`, `minimal`, `nature`.

### 6. Clear the cache

```bash
php bin/adminconsole cache:clear
```

## Configuration

The bundle works with zero configuration. Optional settings can be added in `config/packages/itech_world_sulu_theme.yaml`:

```yaml
itech_world_sulu_theme:
    # Directory where compiled CSS files are stored
    css_output_dir: '%kernel.project_dir%/var/cache/iw_sulu_theme'

    # Public path prefix for serving compiled CSS
    public_css_path: '/build/iw-theme'
```

## Usage

### Admin interface

Navigate to **Settings > Themes** in the Sulu admin panel. From there you can:

1. **Create a theme**: Click "Add", fill in the name and label
2. **Configure colors**: Set primary, secondary, background, text, and link colors
3. **Configure typography**: Add Google Fonts families and assign them to headings/body/links
4. **Configure buttons**: Set primary/secondary button styles (background, text, border, hover states, radius)
5. **Configure borders**: Set border radius values (default, small, large, full, image)
6. **Configure block variants**: Define color schemes (e.g., light, accent, dark) for content blocks
7. **Configure menu**: Choose menu type, colors, animation, and display options
8. **Activate**: Toggle the `isActive` flag to make a theme the active one

### Twig functions

Use these functions in your templates:

```twig
{# Include the compiled theme CSS #}
<link rel="stylesheet" href="{{ iw_sulu_theme_css_path() }}">

{# Include Google Fonts #}
{{ iw_sulu_theme_fonts_link()|raw }}

{# Get the menu configuration #}
{% set menuConfig = iw_sulu_theme_menu_config() %}

{# Get all theme tokens #}
{% set tokens = iw_sulu_theme_tokens() %}

{# Get block style template path #}
{% set template = iw_sulu_block_style_template('gallery', 'grid') %}
```

### CLI commands

```bash
# Install a preset theme and activate it
php bin/adminconsole iw-sulu:theme:install <preset-name>

# Recompile CSS for the active theme (or a specific one)
php bin/adminconsole iw-sulu:theme:compile
php bin/adminconsole iw-sulu:theme:compile --theme=corporate
```

### Security

The bundle registers the security context `sulu.iw_sulu_theme.themes` with VIEW, ADD, EDIT, and DELETE permissions. Configure role access in **Settings > Roles**.

## Architecture

```
SuluThemeBundle/
├── config/
│   ├── forms/              # Sulu admin form XMLs (7 tabs)
│   ├── lists/              # Sulu admin list XML
│   ├── templates/pages/    # Page template XML
│   └── services.yaml       # Service definitions
├── src/
│   ├── Admin/              # ThemeAdmin (navigation, views, security)
│   ├── Command/            # CLI commands (install, compile)
│   ├── Controller/Admin/   # REST API controller
│   ├── DataFixtures/       # Preset theme fixtures
│   ├── Entity/             # ThemeConfig Doctrine entity
│   ├── EventSubscriber/    # Auto-recompile on save
│   ├── Repository/         # ThemeConfigRepository
│   ├── Service/            # ThemeCompiler, ThemeProvider, GoogleFontsResolver
│   └── Twig/               # ThemeExtension
├── templates/              # Twig templates (blocks, menus, base)
├── translations/           # Admin translations (fr, en)
└── assets/admin/           # React components for admin forms
```

## Available translations

* English
* French

## Support

You can support this project by buying me a coffee: [Buy me a coffee](https://www.buymeacoffee.com/steeven.th)

## License

This bundle is under the [MIT License](LICENSE).
