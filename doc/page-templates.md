# Page Templates

The bundle ships with a ready-to-use page template and a modular architecture for creating your own.

## Default page template

The `iw_theme_default` template includes **14 block types**: `text`, `text_images`, `gallery`, `key_figures`, `linked_pages`, `location`, `form`, `document`, `cta`, `testimonial`, `separator`, `article_list`, `article_carousel`, and `article_featured`.

To use it, select **"Page par défaut"** (or **"Default page"**) as the template when creating a page in the Sulu admin.

## Modular architecture

The template system is built on a **modular architecture** that separates concerns:

```
config/templates/
├── pages/
│   └── iw_theme_default.xml              ← Page template (~50 lines, uses <type ref="..."/>)
├── fragments/                       ← Shared property fragments (reference/documentation)
│   ├── header.xml                   ← title + url properties
│   ├── blocks.xml                   ← Block container with all 14 type references
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
    ├── separator.xml
    ├── article_list.xml
    ├── article_carousel.xml
    └── article_featured.xml
```

Each block is a **global Sulu block type** registered via `sulu_admin.templates.block.directories`. The page template references them with `<type ref="text"/>` instead of inlining the full block definition.

## Creating your own page template

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

## Available block types

| Block type | Description | Sections |
|------------|-------------|----------|
| `text` | Rich text content | Content (title group + editor), Appearance, Settings |
| `text_images` | Text with image gallery | Content (title group + images + editor), Appearance, Settings |
| `gallery` | Image gallery | Content (title group + images), Appearance, Settings |
| `key_figures` | Key figures/stats | Content (nested figures block), Appearance, Settings |
| `linked_pages` | Internal/external links | Content (title group + links block), Appearance, Settings |
| `location` | Map with address | Content (title group + coordinates + address), Appearance, Settings |
| `form` | Form integration | Content (title group + SuluFormBundle toggle + form ID or Twig template path), Appearance, Settings |
| `document` | Document downloads | Content (title group + media), Appearance, Settings |
| `cta` | Call to action | Content (title group + buttons + image), Appearance, Settings |
| `testimonial` | Testimonials | Content (title group + testimonials block), Appearance, Settings |
| `separator` | Visual separator | Content (height + line style), Appearance, Settings |
| `article_list` | Article list (grid/list/cards) | Content (title group + smart_content articles + count + pagination), Appearance, Settings |
| `article_carousel` | Article carousel | Content (title group + smart_content articles + count + autoplay + interval), Appearance, Settings |
| `article_featured` | Featured article (hero/side-by-side/spotlight) | Content (title group + smart_content articles), Appearance, Settings |

> The 3 article blocks require `SuluArticleBundle` to be installed. They use `smart_content` with `provider: articles` to fetch articles.

Each block has 3 sections: **Content** (block-specific), **Appearance** (variant + style), and **Settings** (margins, paddings, radius, background).

> All labels use translation keys (`iw_sulu_tailwind_theme.*`). See `translations/admin.fr.json` and `translations/admin.en.json` for the full list.

## Using fragments via XInclude

Instead of manually writing header properties and block lists, you can **include the bundle's fragments** directly in your page template using XML XInclude:

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

        <!-- Include the full blocks container (all 14 types) -->
        <xi:include href="../../../vendor/itech-world/sulu-tailwind-theme-bundle/config/templates/fragments/blocks.xml"
                    xpointer="xmlns(sulu=http://schemas.sulu.io/template/template) xpointer(/sulu:properties/sulu:block)"/>
    </properties>
</template>
```

### Available fragments

| Fragment | Path | Description |
|----------|------|-------------|
| Header | `fragments/header.xml` | `title` (text_line, mandatory, rlp.part) + `url` (route, mandatory, rlp) |
| Blocks | `fragments/blocks.xml` | `<block>` container with all 14 `<type ref="..."/>` |
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

## Excluding the bundle's page template

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
