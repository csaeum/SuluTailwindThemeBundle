<h1 align="center">SuluTailwindThemeBundle</h1>
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
composer require itech-world/sulu-tailwind-theme-bundle
```

For local development with a path repository, add to your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../SuluTailwindThemeBundle"
        }
    ],
    "require": {
        "itech-world/sulu-tailwind-theme-bundle": "dev-dev"
    }
}
```

### 2. Register the bundle

If Symfony Flex doesn't register it automatically, add to `config/bundles.php`:

```php
return [
    // ...
    ItechWorld\SuluTailwindThemeBundle\ItechWorldSuluTailwindThemeBundle::class => ['all' => true],
];
```

### 3. Register routes

Add the following to your `config/routes.yaml`:

```yaml
itech_world_sulu_tailwind_theme:
    resource: '@ItechWorldSuluTailwindThemeBundle/src/Controller/'
    type: attribute
```

### 4. Register frontend assets

The bundle provides Stimulus controllers and CSS that need to be compiled by Webpack Encore.

**Add the npm package** to your project's `package.json`:

```json
{
    "devDependencies": {
        "@itech-world/sulu-tailwind-theme-bundle": "file:vendor/itech-world/sulu-tailwind-theme-bundle/assets"
    }
}
```

**Import the CSS** and add the bundle's templates as a Tailwind source in your `assets/styles/app.css`:

```css
@import "@itech-world/sulu-tailwind-theme-bundle";
@source "../../vendor/itech-world/sulu-tailwind-theme-bundle/templates";
```

> The `@source` directive tells Tailwind CSS 4 to scan the bundle's Twig templates for utility classes. Without it, classes used in menu and block templates won't be compiled.

**Register the Stimulus controllers** in your `assets/controllers.json`:

```json
{
    "controllers": {
        "@itech-world/sulu-tailwind-theme-bundle": {
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

Then install and rebuild your assets:

```bash
npm install
npm run build
```

### 5. Register admin assets

Edit the `assets/admin/package.json` to add the bundle to the list of bundles:
```json
{
    "dependencies": {
        // ...
        "sulu-itech-world-sulu-tailwind-theme-bundle": "file:../../vendor/itech-world/sulu-tailwind-theme-bundle/public/js"
    }
}
```

Edit the `assets/admin/app.js` to add the bundle in imports:
```js
import 'sulu-itech-world-sulu-tailwind-theme-bundle';
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

Available presets: `corporate`, `creative`, `minimal`, `nature`, `halloween`, `christmas`, `megamenu`.

### 8. Clear the cache

```bash
php bin/adminconsole cache:clear
```

## Configuration

The bundle works with zero configuration. Optional settings can be added in `config/packages/itech_world_sulu_tailwind_theme.yaml`:

```yaml
itech_world_sulu_tailwind_theme:
    # Directory where compiled CSS files are stored
    css_output_dir: '%kernel.project_dir%/var/cache/iw_sulu_tailwind_theme'

    # Public path prefix for serving compiled CSS
    public_css_path: '/build/iw-theme'

    # Google Fonts API key for the font picker autocomplete (optional)
    google_fonts_api_key: '%env(GOOGLE_FONTS_API_KEY)%'
```

### Google Fonts API key (optional)

The typography tab includes a **Font Picker** with autocomplete for Google Fonts. To enable it:

1. **Get an API key** from the [Google Cloud Console](https://console.cloud.google.com/apis/credentials):
   - Create a project (or use an existing one)
   - Enable the **Google Fonts Developer API** in [API Library](https://console.cloud.google.com/apis/library/webfonts.googleapis.com)
   - Create an API key in **Credentials**
   - (Recommended) Restrict the key to the **Google Fonts Developer API** only

2. **Add the key to your `.env` file**:

   ```env
   GOOGLE_FONTS_API_KEY=your_api_key_here
   ```

3. **Configure the bundle** in `config/packages/itech_world_sulu_tailwind_theme.yaml`:

   ```yaml
   itech_world_sulu_tailwind_theme:
       google_fonts_api_key: '%env(GOOGLE_FONTS_API_KEY)%'
   ```

4. **Sync the font catalog** (first time or to update):

   ```bash
   php bin/adminconsole iw-sulu:theme:sync-fonts
   ```

   You can also sync from the admin UI by clicking the **sync button (↻)** in the Font Picker.

> **Without an API key**, the Font Picker still works: the Google tab falls back to a free-text input, and the System tab lists 15 cross-platform fonts (Arial, Georgia, Courier New, etc.).

## Usage

### Admin interface

Navigate to **Settings > Themes** in the Sulu admin panel. From there you can:

1. **Create a theme**: Click "Add", fill in the name and label
2. **Configure colors**: Set primary, secondary, background, text, and link colors
3. **Configure typography**: Select fonts for headings/body/accent via the Font Picker (Google Fonts autocomplete, system fonts, or free text)
4. **Configure buttons**: Set primary/secondary button styles (background, text, border, hover states, radius)
5. **Configure borders**: Set border radius values (default, small, large, full, image)
6. **Configure block variants**: Define color schemes (e.g., light, accent, dark) for content blocks
7. **Configure menu**: Choose menu type, colors, animation, and display options
8. **Activate**: Toggle the `isActive` flag to make a theme the active one

### Page templates

The bundle ships with a ready-to-use page template (`iw_theme_default`) that includes **11 block types**: `text`, `text_images`, `gallery`, `key_figures`, `linked_pages`, `location`, `form`, `document`, `cta`, `testimonial`, and `separator`.

To use it, simply select **"Page par défaut"** (or **"Default page"**) as the template when creating a page in the Sulu admin.

#### Modular architecture

The template system is built on a **modular architecture** that separates concerns:

```
config/templates/
├── pages/
│   └── iw_theme_default.xml              ← Page template (~50 lines, uses <type ref="..."/>)
├── fragments/                       ← Shared property fragments (reference/documentation)
│   ├── header.xml                   ← title + url properties
│   ├── blocks.xml                   ← Block container with all 11 type references
│   └── components/
│       ├── title_group.xml          ← title + subtitle + alignment (used by 9/11 blocks)
│       ├── variant.xml              ← Color variant picker (used by 11/11 blocks)
│       └── settings.xml             ← All settings properties (single source of truth)
└── blocks/                          ← Global block types (registered via Sulu DI)
    ├── text.xml
    ├── text_images.xml
    ├── gallery.xml
    ├── key_figures.xml
    ├── linked_pages.xml
    ├── location.xml
    ├── form.xml
    ├── document.xml
    ├── cta.xml
    ├── testimonial.xml
    └── separator.xml
```

Each block is a **global Sulu block type** registered via `sulu_admin.templates.block.directories`. The page template references them with `<type ref="text"/>` instead of inlining the full block definition.

#### Creating your own page template

Since blocks are registered globally, creating a custom page template with a subset of blocks is straightforward:

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

        <!-- Only include the block types you need -->
        <block name="blocks" default-type="text" minOccurs="0">
            <meta>
                <title lang="en">Content blocks</title>
                <title lang="fr">Blocs de contenu</title>
            </meta>
            <types>
                <type ref="text"/>
                <type ref="text_images"/>
                <type ref="gallery"/>
                <!-- Add or remove block types as needed -->
            </types>
        </block>
    </properties>
</template>
```

#### Available block types

| Block type | Description | Sections |
|------------|-------------|----------|
| `text` | Rich text content | Content (title group + editor), Appearance, Settings |
| `text_images` | Text with image gallery | Content (title group + images + editor), Appearance, Settings |
| `gallery` | Image gallery | Content (title group + images), Appearance, Settings |
| `key_figures` | Key figures/stats | Content (nested figures block), Appearance, Settings |
| `linked_pages` | Internal/external links | Content (title group + links block), Appearance, Settings |
| `location` | Map with address | Content (title group + coordinates + address), Appearance, Settings |
| `form` | Form integration | Content (title group + form ID), Appearance, Settings |
| `document` | Document downloads | Content (title group + media), Appearance, Settings |
| `cta` | Call to action | Content (title group + buttons + image), Appearance, Settings |
| `testimonial` | Testimonials | Content (title group + testimonials block), Appearance, Settings |
| `separator` | Visual separator | Content (height + line style), Appearance, Settings |

Each block has 3 sections: **Content** (block-specific), **Appearance** (variant + style), and **Settings** (margins, paddings, radius, background).

> All labels use translation keys (`iw_sulu_tailwind_theme.*`). See `translations/admin.fr.json` and `translations/admin.en.json` for the full list.

#### Using fragments via XInclude

Instead of manually writing header properties and block lists, you can **include the bundle's fragments** directly in your page template using XML XInclude. The `href` must point to the fragment file inside the `vendor/` directory:

```xml
<?xml version="1.0" ?>
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xmlns:xi="http://www.w3.org/2001/XInclude"
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
        <!-- Include header properties (title + url) from the bundle -->
        <xi:include href="../../../vendor/itech-world/sulu-tailwind-theme-bundle/config/templates/fragments/header.xml"
                    xpointer="xmlns(sulu=http://schemas.sulu.io/template/template) xpointer(/sulu:properties/sulu:property)"/>

        <!-- Include the full blocks container (all 11 types) -->
        <xi:include href="../../../vendor/itech-world/sulu-tailwind-theme-bundle/config/templates/fragments/blocks.xml"
                    xpointer="xmlns(sulu=http://schemas.sulu.io/template/template) xpointer(/sulu:properties/sulu:block)"/>
    </properties>
</template>
```

**Available fragments:**

| Fragment | Path | Description |
|----------|------|-------------|
| Header | `fragments/header.xml` | `title` (text_line, mandatory, rlp.part) + `url` (route, mandatory, rlp) |
| Blocks | `fragments/blocks.xml` | `<block>` container with all 11 `<type ref="..."/>` |
| Title group | `fragments/components/title_group.xml` | `title` + `subTitle` + `titleAlignment` (single_select) |
| Variant | `fragments/components/variant.xml` | `variant` (iw_theme_variant_picker) |
| Settings | `fragments/components/settings.xml` | All 9 settings properties (margins, paddings, radius, background) |

> **Note:** The `href` path is relative to your template file location. Adjust `../../../vendor/` according to where your template sits relative to the project root. Typically, for templates in `config/templates/pages/`, the path is `../../../vendor/itech-world/sulu-tailwind-theme-bundle/config/templates/fragments/...`.

You can also **include individual settings properties** using XPointer with a `@name` selector:

```xml
<!-- Include only marginTop from settings.xml -->
<xi:include href="../../../vendor/itech-world/sulu-tailwind-theme-bundle/config/templates/fragments/components/settings.xml"
            xpointer="xmlns(sulu=http://schemas.sulu.io/template/template) xpointer(/sulu:properties/sulu:property[@name='marginTop'])"/>
```

#### Excluding the bundle's page template

If you don't want the bundle's default page template (`iw_theme_default`) to appear in a specific webspace, you can **exclude it** in your webspace XML configuration (`config/webspaces/*.xml`):

```xml
<webspace>
    <!-- ... -->
    <templates>
        <!-- ... -->
    </templates>
    <excluded-templates>
        <excluded-template>iw_theme_default</excluded-template>
    </excluded-templates>
    <!-- ... -->
</webspace>
```

This prevents the "Page par défaut" template from showing up in the page creation dialog for that webspace, while still keeping the **global block types** available for your own page templates via `<type ref="..."/>`.

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
    {{ iw_sulu_tailwind_theme_fonts_link()|raw }}

    {# Theme: compiled CSS custom properties #}
    {% set themeCssPath = iw_sulu_tailwind_theme_css_path() %}
    {% if themeCssPath is not empty %}
        <link rel="stylesheet" href="{{ themeCssPath }}">
    {% endif %}

    {% block style %}{% endblock %}
    {{ encore_entry_link_tags('app') }}
</head>
<body class="bg-[var(--color-background)] text-[var(--color-text)]">
    {# Theme: dynamic menu #}
    {% set menuConfig = iw_sulu_tailwind_theme_menu_config() %}
    {% block header %}
        {% if menuConfig is not empty and menuConfig.type is defined %}
            {% include '@ItechWorldSuluTailwindTheme/menu/_' ~ menuConfig.type ~ '.html.twig' with {config: menuConfig} %}
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
| Google Fonts | `{{ iw_sulu_tailwind_theme_fonts_link()\|raw }}` | Loads font families defined in the theme |
| Theme CSS | `{{ iw_sulu_tailwind_theme_css_path() }}` | Includes compiled CSS custom properties |
| Body classes | `bg-[var(--color-background)] text-[var(--color-text)]` | Applies theme background and text colors |
| Dynamic menu | `iw_sulu_tailwind_theme_menu_config()` | Renders the menu type configured in the admin |
| Menu templates | `@ItechWorldSuluTailwindTheme/menu/_<type>.html.twig` | Available types: `navbar`, `burger`, `fullscreen`, `sidebar` |

> The bundle also provides a `@ItechWorldSuluTailwindTheme/base.html.twig` template that you can extend if you prefer, but integrating the functions directly gives you more flexibility.

### Twig functions

Use these functions in your templates:

```twig
{# Include the compiled theme CSS #}
<link rel="stylesheet" href="{{ iw_sulu_tailwind_theme_css_path() }}">

{# Include Google Fonts #}
{{ iw_sulu_tailwind_theme_fonts_link()|raw }}

{# Get the menu configuration #}
{% set menuConfig = iw_sulu_tailwind_theme_menu_config() %}

{# Get all theme tokens #}
{% set tokens = iw_sulu_tailwind_theme_tokens() %}

{# Get block styles configuration #}
{% set blockStyles = iw_sulu_tailwind_theme_block_styles() %}

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

# Sync the Google Fonts catalog (requires API key)
php bin/adminconsole iw-sulu:theme:sync-fonts
```

### Security

The bundle registers the security context `sulu.iw_sulu_tailwind_theme.themes` with VIEW, ADD, EDIT, and DELETE permissions. Configure role access in **Settings > Roles**.

## Using the theme in custom components

The theme compiles design tokens into **CSS custom properties** and exposes data through **Twig functions** and a **global variable**. This means your custom Twig templates and CSS automatically adapt when the active theme changes.

Quick example:

```css
/* Your custom CSS — adapts to the active theme */
.my-card {
    background: var(--color-primary-50);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    font-family: var(--font-family-body);
}
```

```twig
{# Your custom Twig — variant colors applied automatically #}
<section class="block-variant-0" data-has-bg="true">
    <h2>Title is colored by the variant</h2>
    <p>Paragraph too.</p>
    <a href="/cta" class="btn-primary px-6 py-3">Themed button</a>
</section>
```

For the full reference, see the **[doc/](doc/)** directory:

| Document | Description |
|----------|-------------|
| [CSS Variables Reference](doc/css-variables.md) | All CSS custom properties: colors, palettes, typography, borders, buttons, menu |
| [Block Variants](doc/block-variants.md) | Variant classes, auto-styled elements, separator styles, `.btn-variant` |
| [Twig Reference](doc/twig-reference.md) | All Twig functions, global variable `iw_sulu_tailwind_theme`, token structure |
| [Custom Integration Guide](doc/custom-integration.md) | Step-by-step examples: custom CSS, Twig components, block templates, PHP services |
| [Menus](doc/menus.md) | Menu types, configuration, and customization |

## Architecture

```
SuluTailwindThemeBundle/
├── config/
│   ├── forms/              # Sulu admin form XMLs (7 tabs)
│   ├── lists/              # Sulu admin list XML
│   ├── templates/
│   │   ├── pages/          # Page template XML (uses <type ref="..."/>)
│   │   ├── blocks/         # Global block type definitions (11 types)
│   │   └── fragments/      # Shared property fragments (reference)
│   └── services.yaml       # Service definitions
├── src/
│   ├── Admin/              # ThemeAdmin (navigation, views, security)
│   ├── Command/            # CLI commands (install, compile, sync-fonts)
│   ├── Controller/Admin/   # REST API controller
│   ├── DataFixtures/       # Preset theme fixtures
│   ├── Entity/             # ThemeConfig Doctrine entity
│   ├── EventSubscriber/    # Auto-recompile on save
│   ├── Repository/         # ThemeConfigRepository
│   ├── Service/            # ThemeCompiler, ThemeProvider, GoogleFontsResolver, GoogleFontsCatalog
│   └── Twig/               # ThemeExtension
├── templates/              # Twig templates (blocks, menus, base)
├── translations/           # Admin translations (fr, en)
├── assets/                 # Frontend assets (Stimulus controllers, CSS)
└── public/js/              # Admin React components
```

## Available translations

* English
* French
* German

## 🐛 Bug and Idea

See the [open issues](https://github.com/steeven-th/SuluTailwindThemeBundle/issues) for a list of proposed features (and known issues).

## 💰 Support me

You can buy me a coffee to support me **this plugin is 100% free**.

[Buy me a coffee](https://www.buymeacoffee.com/steeven.th)

## 👨‍💻 Contact

<a href="https://steeven-th.dev"><img src="https://avatars.githubusercontent.com/u/82022828?s=96&v=4" width="50"></a>
<a href="https://x.com/ThomasSteeven2"><img src="./doc/images/x.webp" width="50" alt="x.com"></a>
<a href="https://www.linkedin.com/in/steeven-thomas-221b02b8/"><img src="./doc/images/linkedin.png" width="50" alt="Linkedin"></a>

## 📘&nbsp; License

This bundle is under the [MIT License](LICENSE).
