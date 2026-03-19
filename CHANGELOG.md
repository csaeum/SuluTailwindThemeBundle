# [](https://github.com/steeven-th/SuluTailwindThemeBundle/compare/v1.2.0...v) (2026-03-19)


* feat!: add multi-webspace theme support ([0362156](https://github.com/steeven-th/SuluTailwindThemeBundle/commit/03621560f7a5599020a20be99b91bd9bd08753c1))


### Bug Fixes

* add webspaces column to theme list with post-query enrichment ([d42c29c](https://github.com/steeven-th/SuluTailwindThemeBundle/commit/d42c29c072494c91d7c5c605109bb5f8afaaefc9))


### Features

* add per-webspace theme config resolution in admin JS ([1f37f3c](https://github.com/steeven-th/SuluTailwindThemeBundle/commit/1f37f3ca8f570dfc752ffaea8e0ffdb305f5d5a1))


### BREAKING CHANGES

* removes isActive from ThemeConfig, replaces global
theme activation with per-webspace assignment via WebspaceTheme entity.
Run iw-sulu:theme:migrate-webspaces before doctrine:schema:update.
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-19

### Added

- **Multi-webspace theme support**: assign different themes to different webspaces (sites) in a multi-site Sulu installation
- New `WebspaceTheme` entity (junction table: webspaceKey → ThemeConfig)
- New "Theme" tab in webspace settings (following the SnippetAreaAdmin pattern)
- New `WebspaceThemeController` API for webspace theme assignment (GET/PUT/DELETE)
- New `WebspaceThemeAdmin` with per-webspace security contexts
- New `WebspaceThemeForm` React component for theme selection in webspace tabs
- New `iw-sulu:theme:migrate-webspaces` migration command for upgrading from isActive
- `--webspace` option on `iw-sulu:theme:install` command
- `iw-sulu:theme:compile` now compiles all themes when no `--theme` is specified
- "Webspaces" column in the admin theme list (post-query enrichment)
- Translation keys for webspace theme UI (en, fr, de)
- Upgrade guide in README

### Changed

- `ThemeProvider` now resolves theme per webspace via `RequestAnalyzerInterface`
- `ThemeAdmin::getConfig()` picks the first webspace-assigned theme as default config
- `ThemeCompileSubscriber` only recompiles themes assigned to at least one webspace
- `SaveWithConfigReloadAction` now reloads only the bundle config (no longer triggers full admin re-initialization that broke navigation)
- Multiple webspaces sharing the same theme share one compiled CSS file

### Removed

- **BREAKING**: `isActive` field, getter, and setter from `ThemeConfig` entity
- **BREAKING**: `findActive()` and `deactivateAll()` from `ThemeConfigRepository`
- **BREAKING**: `activateAction()` endpoint from `ThemeConfigController`
- **BREAKING**: "Activate" toolbar action from the admin theme list
- **BREAKING**: `ActivateToolbarAction` React component
- `isActive` checkbox from theme details form
- `isActive` column from theme list XML

### Migration

```bash
# 1. Update the bundle
composer update itech-world/sulu-tailwind-theme-bundle

# 2. Migrate existing active theme to all webspaces (BEFORE schema update)
php bin/adminconsole iw-sulu:theme:migrate-webspaces

# 3. Update the database schema
php bin/adminconsole doctrine:schema:update --force

# 4. Rebuild admin assets
cd assets/admin && npm install && npm run build

# 5. Clear the cache
php bin/adminconsole cache:clear

# 6. Update admin roles to include new per-webspace security contexts
```

## [1.2.0] - 2026-03-19

### Added

- Color reference system for semantic palette linking (`ref:primary-3`, `ref:accent-5`)
- Button color properties can reference OKLCH palette shades instead of hardcoded hex values
- `ThemeAdmin::getConfig()` resolves `ref:` values to hex before sending to the frontend

## [1.1.1] - 2026-03-16

### Fixed

- Use form data instead of global config for palette and button previews in the admin editor

## [1.1.0] - 2026-03-16

### Added

- Form block type with SuluFormBundle integration (dynamic detection)
- Combobox and file input Stimulus controllers
- Missing dependencies in `composer.json` and `package.json`

## [1.0.1] - 2026-03-16

### Fixed

- Remove conflicting relative class from fullscreen menu panel without image

## [1.0.0] - 2026-03-05

### Added

- Initial release of the SuluTailwindThemeBundle
- Design token system (JSON → CSS custom properties)
- Admin interface with 7 tabs (details, colors, typography, buttons, borders, variants, menu)
- 7 preset themes (corporate, creative, minimal, nature, halloween, christmas, megamenu)
- OKLCH palette generation from 4 main colors
- Google Fonts integration with Font Picker (autocomplete + system/local fallbacks)
- Block variant system with per-variant color schemes
- 11 block types (text, text_images, gallery, key_figures, linked_pages, location, form, document, cta, testimonial, separator)
- 5 menu types (navbar, burger, fullscreen, sidebar, megamenu)
- Modular page template architecture with XInclude fragments
- Stimulus controllers (lightbox, menu, slider, carousel3d, key_figures, location_overlay)
- CLI commands (install presets, compile CSS, sync Google Fonts)
- Auto-recompile via Doctrine listener
- Translations (English, French, German)

[2.0.0]: https://github.com/steeven-th/SuluTailwindThemeBundle/compare/v1.2.0...v2.0.0
[1.2.0]: https://github.com/steeven-th/SuluTailwindThemeBundle/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/steeven-th/SuluTailwindThemeBundle/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/steeven-th/SuluTailwindThemeBundle/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/steeven-th/SuluTailwindThemeBundle/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/steeven-th/SuluTailwindThemeBundle/releases/tag/v1.0.0
