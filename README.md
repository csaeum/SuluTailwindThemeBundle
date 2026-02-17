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

### 4. Register frontend assets

The bundle provides Stimulus controllers and CSS that need to be compiled by Webpack Encore.

**Import the CSS** in your `assets/styles/app.css`:

```css
@import "@itech-world/sulu-theme-bundle";
```

**Register the Stimulus controllers** in your `assets/controllers.json`:

```json
{
    "controllers": {
        "@itech-world/sulu-theme-bundle": {
            "menu": {
                "enabled": true,
                "fetch": "lazy"
            },
            "gallery": {
                "enabled": true,
                "fetch": "lazy"
            },
            "slider": {
                "enabled": true,
                "fetch": "lazy"
            },
            "key_figures": {
                "enabled": true,
                "fetch": "lazy"
            }
        }
    },
    "entrypoints": []
}
```

**Configure Webpack** to disable symlink resolution in your `webpack.config.js`:

```js
// Replace the last line:
// module.exports = Encore.getWebpackConfig();

// With:
const config = Encore.getWebpackConfig();
config.resolve.symlinks = false;
module.exports = config;
```

> This is required so that Webpack treats the bundle's Stimulus controllers as `node_modules` files (skipping Babel transpilation) and resolves their dependencies correctly.

Then rebuild your assets:

```bash
npm run build
```

### 5. Register admin assets

Edit the `assets/admin/package.json` to add the bundle to the list of bundles:
```json
{
    "dependencies": {
        // ...
        "sulu-itech-world-sulu-theme-bundle": "file:../../vendor/itech-world/sulu-theme-bundle/public/js"
    }
}
```

Edit the `assets/admin/app.js` to add the bundle in imports:
```js
import 'sulu-itech-world-sulu-theme-bundle';
```

In the `assets/admin/` folder, run the following command:
```bash
npm install
npm run build
```

or

```bash
yarn install
yarn build
```

### 6. Update the database schema

```bash
php bin/adminconsole doctrine:schema:update --force
```

### 7. Install a preset theme (optional)

```bash
php bin/adminconsole iw-sulu:theme:install corporate
```

Available presets: `corporate`, `creative`, `minimal`, `nature`.

### 8. Clear the cache

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

### Page templates

The bundle ships with a ready-to-use page template (`iw_default`) that includes 8 block types: `text`, `text_images`, `gallery`, `key_figures`, `linked_pages`, `location`, `form`, and `document`.

To use it, simply select **"Page par défaut"** (or **"Default page"**) as the template when creating a page in the Sulu admin.

#### Creating your own page template

If you want your own page template while reusing the bundle's block types, create an XML template in your project's `config/templates/pages/` directory:

```xml
<?xml version="1.0" ?>
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/template-1.0.xsd">

    <key>my_page</key>
    <view>pages/my_page</view>
    <controller>Sulu\Content\UserInterface\Controller\Website\ContentController::indexAction</controller>
    <cacheLifetime>604800</cacheLifetime>

    <meta>
        <title lang="en">My custom page</title>
        <title lang="fr">Ma page personnalisée</title>
    </meta>

    <properties>
        <property name="title" type="text_line" mandatory="true">
            <meta>
                <title lang="en">Title</title>
                <title lang="fr">Titre</title>
            </meta>
            <params>
                <param name="headline" value="true"/>
            </params>
            <tag name="sulu.rlp.part"/>
        </property>

        <property name="url" type="route" mandatory="true">
            <meta>
                <title lang="en">URL</title>
                <title lang="fr">URL</title>
            </meta>
            <tag name="sulu.rlp"/>
        </property>

        <!-- Content blocks -->
        <block name="blocks" default-type="text" minOccurs="0">
            <meta>
                <title lang="en">Content blocks</title>
                <title lang="fr">Blocs de contenu</title>
            </meta>

            <types>
                <!-- Example: text block -->
                <type name="text">
                    <meta>
                        <title lang="en">Text</title>
                        <title lang="fr">Texte</title>
                    </meta>
                    <properties>
                        <property name="title" type="text_line">
                            <meta><title lang="en">Title</title><title lang="fr">Titre</title></meta>
                        </property>
                        <property name="subTitle" type="text_line">
                            <meta><title lang="en">Subtitle</title><title lang="fr">Sous-titre</title></meta>
                        </property>
                        <property name="text" type="text_editor">
                            <meta><title lang="en">Text</title><title lang="fr">Texte</title></meta>
                        </property>
                        <!-- Appearance properties (used by the theme system) -->
                        <property name="variant" type="text_line">
                            <meta><title lang="en">Color variant</title><title lang="fr">Variante de couleur</title></meta>
                        </property>
                        <property name="style" type="text_line">
                            <meta><title lang="en">Layout style</title><title lang="fr">Style d'agencement</title></meta>
                        </property>
                        <property name="marginTop" type="text_line">
                            <meta><title lang="en">Top margin</title><title lang="fr">Marge haute</title></meta>
                        </property>
                        <property name="marginBottom" type="text_line">
                            <meta><title lang="en">Bottom margin</title><title lang="fr">Marge basse</title></meta>
                        </property>
                        <property name="lateralMargins" type="checkbox">
                            <meta><title lang="en">Lateral margins</title><title lang="fr">Marges latérales</title></meta>
                        </property>
                        <property name="showBackground" type="checkbox">
                            <meta><title lang="en">Background color</title><title lang="fr">Couleur de fond</title></meta>
                        </property>
                    </properties>
                </type>

                <!-- Add more block types as needed (text_images, gallery, etc.) -->
                <!-- See config/templates/pages/iw_default.xml for all available block types -->
            </types>
        </block>
    </properties>
</template>
```

Each block type supports these **appearance properties** for the theme system:

| Property | Type | Description |
|----------|------|-------------|
| `variant` | `text_line` | Color variant name (e.g., `clair`, `accent`, `sombre`) |
| `style` | `text_line` | Layout style (e.g., `centered`, `grid`, `overlay`) |
| `marginTop` | `text_line` | Top margin (Tailwind spacing value) |
| `marginBottom` | `text_line` | Bottom margin (Tailwind spacing value) |
| `lateralMargins` | `checkbox` | Enable lateral container margins |
| `showBackground` | `checkbox` | Show the variant background color |

> For the full list of block types and their properties, refer to `config/templates/pages/iw_default.xml`.

#### Integrating the theme in your base template

The recommended approach is to integrate the theme Twig functions directly into your project's `templates/base.html.twig`. This gives you full control over the layout while benefiting from the theme system.

Here is a complete example:

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html lang="{{ app.request.locale|split('_')[0] }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {% block meta %}
        {{ include('@SuluWebsite/Extension/seo.html.twig', {
            seo: extension.seo|default([]),
            content: content|default([]),
            localizations: localizations|default([]),
            shadowBaseLocale: shadowBaseLocale|default(),
        }) }}
    {% endblock %}

    {# Theme: Google Fonts #}
    {{ iw_sulu_theme_fonts_link()|raw }}

    {# Theme: compiled CSS custom properties #}
    {% set themeCssPath = iw_sulu_theme_css_path() %}
    {% if themeCssPath is not empty %}
        <link rel="stylesheet" href="{{ themeCssPath }}">
    {% endif %}

    {% block style %}{% endblock %}
    {{ encore_entry_link_tags('app') }}
</head>
<body class="bg-[var(--color-background)] text-[var(--color-text)]">
    {# Theme: dynamic menu #}
    {% set menuConfig = iw_sulu_theme_menu_config() %}
    {% block header %}
        {% if menuConfig is not empty and menuConfig.type is defined %}
            {% include '@ItechWorldSuluTheme/menu/_' ~ menuConfig.type ~ '.html.twig' with {config: menuConfig} %}
        {% else %}
            <header>
                <nav class="container mx-auto px-4 py-4">
                    <ul class="flex gap-4">
                        <li><a href="{{ sulu_content_root_path() }}">Home</a></li>
                        {% for item in sulu_page_navigation_root_tree('main', 1, {title: 'title', url: 'url'}) %}
                            <li>
                                <a href="{{ sulu_content_path(item.url) }}" title="{{ item.title }}">{{ item.title }}</a>
                            </li>
                        {% endfor %}
                    </ul>
                </nav>
            </header>
        {% endif %}
    {% endblock %}

    <main>
        {% block content %}{% endblock %}
    </main>

    <footer>
        {% block footer %}
            <p>Copyright {{ 'now'|date('Y') }} SULU</p>
        {% endblock %}
    </footer>

    {% block javascripts %}{% endblock %}
    {{ encore_entry_script_tags('app') }}
</body>
</html>
```

**Key integration points:**

| Element | Code | Purpose |
|---------|------|---------|
| Google Fonts | `{{ iw_sulu_theme_fonts_link()\|raw }}` | Loads font families defined in the theme |
| Theme CSS | `{{ iw_sulu_theme_css_path() }}` | Includes compiled CSS custom properties |
| Body classes | `bg-[var(--color-background)] text-[var(--color-text)]` | Applies theme background and text colors |
| Dynamic menu | `iw_sulu_theme_menu_config()` | Renders the menu type configured in the admin |
| Menu templates | `@ItechWorldSuluTheme/menu/_<type>.html.twig` | Available types: `navbar`, `burger`, `fullscreen`, `sidebar` |

> The bundle also provides a `@ItechWorldSuluTheme/base.html.twig` template that you can extend if you prefer, but integrating the functions directly gives you more flexibility.

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

{# Get block styles configuration #}
{% set blockStyles = iw_sulu_theme_block_styles() %}

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
