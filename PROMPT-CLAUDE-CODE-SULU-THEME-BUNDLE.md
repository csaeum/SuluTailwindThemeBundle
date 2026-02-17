# PROMPT CLAUDE CODE — Création du bundle SuluThemeBundle pour Sulu 3.0

## CONTEXTE GÉNÉRAL

Tu travailles dans un projet **Sulu CMS 3.0.4** vierge (installation fraîche). Référence du code source Sulu : https://github.com/sulu/sulu/tree/3.0.4 — utilise ce repo comme source de vérité pour toutes les conventions, interfaces, et patterns à respecter.

Tu dois créer un bundle Symfony nommé **SuluThemeBundle** (namespace `ItechWorld\SuluThemeBundle`) qui fournit un système de theming complet, inspiré du concept de "thèmes WordPress" mais adapté à l'architecture Sulu.

**Objectif métier** : permettre à une agence web de déployer rapidement de nouveaux sites Sulu en configurant un thème (couleurs, typographie, boutons, bordures, variantes de blocs, menus) depuis l'admin, sans toucher au code. Les pages sont construites avec des blocs réutilisables dont seule l'apparence change d'un projet à l'autre.

**Architecture cible** : un système de "design tokens" stockés en JSON dans une seule entité Doctrine, compilés en CSS custom properties, et cachés. Zéro FK vers des entités couleur, zéro migration quand on ajoute une propriété de style.

**IMPORTANT** : le bundle fait parti de ItechWorld, penser à utiliser `iw` afin d'éviter les conflits avec d'autres bundles, notamment sur des commandes CLI, routes, fixtures, fichiers de configuration, etc.

---

## ORGANISATION DU TRAVAIL — 3 AGENTS MINIMUM

Découpe le travail en **au moins 3 agents** travaillant sur des périmètres distincts :

### Agent 1 : Backend Symfony — Entités, API, Services, Compilation

Responsable de :
- L'entité `ThemeConfig` et son repository
- Les controllers API REST admin (CRUD thèmes, gestion variantes, gestion fonts)
- Le service `ThemeCompiler` qui génère le CSS depuis le JSON
- Le système de cache et d'invalidation
- Les commandes CLI (installation de thème, compilation, etc.)
- Les fixtures de thèmes prédéfinis
- La configuration du bundle (services, routes, security contexts)
- Les event listeners/subscribers

### Agent 2 : Admin Sulu — Vues, Navigation, Formulaires, Composants React

Responsable de :
- Les classes Admin PHP (navigation, vues, permissions)
- Les fichiers de configuration XML des formulaires et listes
- Les composants React custom pour l'admin (color picker amélioré, variant preview, font selector, wireframe selector, margin selector)
- L'intégration JavaScript (index.js, registries, field types)
- Le respect strict des conventions Sulu 3.0 pour l'admin UI

### Agent 3 : Front-end — Templates Twig, Blocs, CSS, Stimulus

**Stack front déjà en place dans le socle** :
- **Webpack Encore** pour la compilation des assets (config déjà fonctionnelle)
- **Tailwind CSS 4** pour les utilitaires CSS (déjà installé et configuré)
- L'agent n'a PAS à configurer Webpack Encore ni Tailwind, tout est prêt. Il doit simplement utiliser ces outils.

Responsable de :
- La feuille de base CSS utilisant les CSS custom properties générées par le ThemeCompiler
- Les templates Twig des blocs (tous les types)
- Les templates de menus
- Les templates de pages (base layout, page par défaut)
- Les controllers Stimulus JS (enregistrés via Webpack Encore / Stimulus bridge)
- L'utilisation des classes utilitaires Tailwind CSS 4 dans les templates (marges `mt-*`, `mb-*`, `px-*`, container, responsive, etc.)
- Les templates de fragments réutilisables (_titles, _paragraph, _image, etc.)

**Note Tailwind CSS 4** : Tailwind 4 utilise une approche CSS-first (plus de fichier `tailwind.config.js`). La configuration se fait via `@theme` dans le CSS. Les classes utilitaires Tailwind doivent être utilisées pour les marges, paddings, containers, responsive, flex/grid, mais les couleurs et typographies proviennent des CSS custom properties générées par le ThemeCompiler (pas des couleurs Tailwind). Ne PAS dupliquer les tokens dans la config Tailwind — utiliser `var(--color-primary)` etc. directement dans le CSS custom ou via des classes CSS générées.

**Chaque agent doit systématiquement vérifier les conventions de Sulu 3.0 dans les vendors (`vendor/sulu/sulu/`) avant d'implémenter**. Regarder comment Sulu fait les choses en interne pour les reproduire (pattern Admin, pattern Controller REST, pattern ContentType, etc.).

---

## STRUCTURE DU BUNDLE

```
bundles/ItechWorld\SuluThemeBundle/
├── config/
│   ├── forms/                          # XML des formulaires admin
│   │   ├── theme_config_details.xml    # Formulaire principal du thème
│   │   ├── theme_config_colors.xml     # Onglet couleurs
│   │   ├── theme_config_typography.xml # Onglet typographie
│   │   ├── theme_config_buttons.xml    # Onglet boutons
│   │   ├── theme_config_borders.xml    # Onglet bordures/formes
│   │   ├── theme_config_variants.xml   # Onglet variantes de blocs
│   │   └── theme_config_menu.xml       # Onglet configuration menu
│   ├── lists/
│   │   └── theme_configs.xml           # Liste des thèmes
│   ├── templates/
│   │   ├── blocks/                     # Définitions XML des block types Sulu
│   │   │   ├── text.xml
│   │   │   ├── text_images.xml
│   │   │   ├── gallery.xml
│   │   │   ├── key_figures.xml
│   │   │   ├── linked_pages.xml
│   │   │   ├── location.xml
│   │   │   ├── form.xml
│   │   │   └── document.xml
│   │   ├── pages/
│   │   │   └── default.xml            # Template de page par défaut
│   │   └── snippets/                   # Snippets réutilisables
│   ├── routes/
│   │   └── admin.yaml                  # Routes API admin
│   ├── services.yaml                   # Définitions de services
│   └── image-formats.xml              # Formats d'images custom
├── src/
│   ├── Admin/
│   │   └── ThemeAdmin.php       # Navigation + Vues + Security
│   ├── Command/
│   │   ├── ThemeInstallCommand.php     # bin/console iw-sulu:theme:install <name>
│   │   └── ThemeCompileCommand.php     # bin/console iw-sulu:theme:compile
│   ├── Controller/
│   │   └── Admin/
│   │       └── ThemeConfigController.php  # API REST CRUD
│   ├── Entity/
│   │   └── ThemeConfig.php
│   ├── Repository/
│   │   └── ThemeConfigRepository.php
│   ├── Service/
│   │   ├── ThemeCompiler.php           # JSON → CSS custom properties
│   │   ├── ThemeProvider.php           # Fournit le thème actif au front
│   │   └── GoogleFontsResolver.php     # Génère les imports Google Fonts
│   ├── EventSubscriber/
│   │   └── ThemeCompileSubscriber.php  # Recompile on save
│   ├── Twig/
│   │   └── ThemeExtension.php         # Fonctions/variables Twig
│   ├── DataFixtures/
│   │   └── ThemeFixtures.php          # Thèmes prédéfinis
│   └── ItechWorldSuluThemeBundle.php       # Classe du bundle
├── templates/                          # Templates Twig front
│   ├── base.html.twig                 # Layout de base
│   ├── blocks/                        # Un dossier par type de bloc
│   │   ├── text/
│   │   │   ├── _style_centered.html.twig
│   │   │   ├── _style_left_aligned.html.twig
│   │   │   └── _style_two_columns.html.twig
│   │   ├── text_images/
│   │   │   ├── _style_classic.html.twig
│   │   │   ├── _style_overlay.html.twig
│   │   │   ├── _style_fullwidth.html.twig
│   │   │   ├── _style_mosaic.html.twig
│   │   │   └── _style_sidebar.html.twig
│   │   ├── gallery/
│   │   │   ├── _style_grid.html.twig
│   │   │   ├── _style_masonry.html.twig
│   │   │   ├── _style_slider.html.twig
│   │   │   ├── _style_carousel.html.twig
│   │   │   ├── _style_lightbox_grid.html.twig
│   │   │   └── _style_fullscreen_slider.html.twig
│   │   ├── key_figures/
│   │   │   ├── _style_inline.html.twig
│   │   │   ├── _style_with_icons.html.twig
│   │   │   └── _style_grid_2x2.html.twig
│   │   ├── linked_pages/
│   │   │   ├── _style_cards.html.twig
│   │   │   ├── _style_list.html.twig
│   │   │   ├── _style_horizontal.html.twig
│   │   │   ├── _style_featured.html.twig
│   │   │   └── _style_minimal.html.twig
│   │   ├── location/
│   │   │   ├── _style_map_only.html.twig
│   │   │   └── _style_map_with_info.html.twig
│   │   ├── form/
│   │   │   ├── _style_centered.html.twig
│   │   │   └── _style_split.html.twig
│   │   ├── document/
│   │   │   └── _style_default.html.twig
│   │   └── common/                    # Fragments partagés
│   │       ├── _block_wrapper.html.twig
│   │       ├── _titles.html.twig
│   │       ├── _paragraph.html.twig
│   │       ├── _image.html.twig
│   │       └── _button.html.twig
│   └── menu/
│       ├── _navbar.html.twig
│       ├── _burger.html.twig
│       ├── _fullscreen.html.twig
│       └── _sidebar.html.twig
├── assets/
│   └── admin/
│       ├── index.js                   # Point d'entrée JS admin
│       └── components/                # Composants React custom
│           ├── VariantPicker/
│           │   └── VariantPicker.js   # Wireframes colorés
│           ├── StylePicker/
│           │   └── StylePicker.js     # Sélection wireframe
│           ├── MarginSelector/
│           │   └── MarginSelector.js  # Sélection marges
│           ├── ColorTokenEditor/
│           │   └── ColorTokenEditor.js # Édition couleur + preview
│           └── FontSelector/
│               └── FontSelector.js    # Recherche Google Fonts
└── public/
    └── css/
        └── theme-base.css             # CSS de base (vars + classes utilitaires)
```

---

## ENTITÉ ThemeConfig — SPÉCIFICATION DÉTAILLÉE

### Champs

```php
#[ORM\Entity(repositoryClass: ThemeConfigRepository::class)]
#[ORM\Table(name: 'iw_sulu_theme_config')]
#[ORM\HasLifecycleCallbacks]
class ThemeConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom unique du thème : "corporate", "creative", "minimal", etc.
    #[ORM\Column(length: 64, unique: true)]
    private string $name;

    // Label affiché dans l'admin
    #[ORM\Column(length: 128)]
    private string $label;

    // Design tokens — LE CŒUR DU SYSTÈME (voir JSON schema ci-dessous)
    #[ORM\Column(type: Types::JSON)]
    private array $tokens = [];

    // Configuration du menu (type, couleurs, options)
    #[ORM\Column(type: Types::JSON)]
    private array $menuConfig = [];

    // Styles de blocs disponibles et leur mapping vers les fichiers twig
    #[ORM\Column(type: Types::JSON)]
    private array $blockStyles = [];

    // Thème actif pour le site
    #[ORM\Column]
    private bool $isActive = false;

    // Timestamps
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    // Créateur / Modifieur (FK vers Sulu User si besoin)
    #[ORM\Column(nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $changedBy = null;
}
```

### JSON Schema — tokens

```json
{
  "colors": {
    "primary": "#1a56db",
    "secondary": "#7c3aed",
    "background": "#ffffff",
    "text": "#1f2937",
    "link": "#1a56db",
    "linkHover": "#1e40af"
  },

  "typography": {
    "families": {
      "heading": {
        "family": "Inter",
        "source": "google",
        "weights": [400, 600, 700, 800]
      },
      "body": {
        "family": "Inter",
        "source": "google",
        "weights": [400, 500, 600]
      },
      "accent": {
        "family": "DM Mono",
        "source": "google",
        "weights": [400]
      }
    },
    "assignments": {
      "h1": { "family": "heading", "weight": 700, "style": "normal" },
      "h2": { "family": "heading", "weight": 600, "style": "normal" },
      "h3": { "family": "heading", "weight": 600, "style": "normal" },
      "h4": { "family": "heading", "weight": 500, "style": "normal" },
      "h5": { "family": "body", "weight": 600, "style": "normal" },
      "h6": { "family": "body", "weight": 600, "style": "normal" },
      "body": { "family": "body", "weight": 400, "style": "normal" },
      "link": { "family": "body", "weight": 500, "style": "normal" }
    }
  },

  "borders": {
    "radius": "8px",
    "radiusSm": "4px",
    "radiusLg": "16px",
    "radiusFull": "9999px",
    "imageRadius": "8px"
  },

  "buttons": {
    "primary": {
      "bg": "#1a56db",
      "text": "#ffffff",
      "border": "#1a56db",
      "hoverBg": "#1544b8",
      "hoverText": "#ffffff",
      "hoverBorder": "#1544b8",
      "radius": "8px"
    },
    "secondary": {
      "bg": "transparent",
      "text": "#1a56db",
      "border": "#1a56db",
      "hoverBg": "#1a56db",
      "hoverText": "#ffffff",
      "hoverBorder": "#1a56db",
      "radius": "8px"
    }
  },

  "blockVariants": {
    "clair": {
      "label": "Clair",
      "title": "#1f2937",
      "subtitle": "#4b5563",
      "paragraph": "#4b5563",
      "link": "#1a56db",
      "list": "#4b5563",
      "hr": "#e5e7eb",
      "paragraphBg": "transparent",
      "blockBg": "#ffffff"
    },
    "accent": {
      "label": "Accent primaire",
      "title": "#ffffff",
      "subtitle": "#dbeafe",
      "paragraph": "#dbeafe",
      "link": "#ffffff",
      "list": "#dbeafe",
      "hr": "rgba(255,255,255,0.2)",
      "paragraphBg": "rgba(255,255,255,0.08)",
      "blockBg": "#1a56db"
    },
    "sombre": {
      "label": "Sombre",
      "title": "#ffffff",
      "subtitle": "#d1d5db",
      "paragraph": "#d1d5db",
      "link": "#60a5fa",
      "list": "#d1d5db",
      "hr": "#374151",
      "paragraphBg": "#1f2937",
      "blockBg": "#111827"
    }
  }
}
```

### JSON Schema — menuConfig

```json
{
  "type": "navbar",
  "animation": "none",
  "clickParentPage": true,
  "childLevels": 3,
  "displayLogoDesktop": true,
  "displayLogoMobile": true,
  "displaySiteName": false,
  "displaySocialMedia": false,
  "colors": {
    "bg": "#ffffff",
    "text": "#1f2937",
    "textHover": "#1a56db",
    "secondBg": "#f8fafc",
    "secondText": "#475569",
    "secondTextHover": "#1a56db",
    "thirdBg": "#f1f5f9",
    "thirdText": "#64748b",
    "divider": "#e2e8f0",
    "burgerOpen": "#1f2937",
    "burgerClose": "#ffffff",
    "socialMedia": "#94a3b8",
    "socialMediaHover": "#1a56db"
  }
}
```

### JSON Schema — blockStyles

Ce champ définit quels styles d'agencement sont disponibles par type de bloc, et leur fichier Twig associé :

```json
{
  "text": {
    "enabled": true,
    "styles": [
      { "key": "centered", "label": "Centré", "twig": "_style_centered.html.twig", "default": true },
      { "key": "left_aligned", "label": "Aligné gauche", "twig": "_style_left_aligned.html.twig" },
      { "key": "two_columns", "label": "Deux colonnes", "twig": "_style_two_columns.html.twig" }
    ]
  },
  "text_images": {
    "enabled": true,
    "styles": [
      { "key": "classic", "label": "Classique", "twig": "_style_classic.html.twig", "default": true },
      { "key": "overlay", "label": "Superposé", "twig": "_style_overlay.html.twig" },
      { "key": "fullwidth", "label": "Pleine largeur", "twig": "_style_fullwidth.html.twig" },
      { "key": "mosaic", "label": "Mosaïque", "twig": "_style_mosaic.html.twig" },
      { "key": "sidebar", "label": "Bande latérale", "twig": "_style_sidebar.html.twig" }
    ]
  },
  "gallery": {
    "enabled": true,
    "styles": [
      { "key": "grid", "label": "Grille", "twig": "_style_grid.html.twig", "default": true },
      { "key": "masonry", "label": "Masonry", "twig": "_style_masonry.html.twig" },
      { "key": "slider", "label": "Slider", "twig": "_style_slider.html.twig" },
      { "key": "carousel", "label": "Carrousel", "twig": "_style_carousel.html.twig" },
      { "key": "lightbox_grid", "label": "Grille Lightbox", "twig": "_style_lightbox_grid.html.twig" },
      { "key": "fullscreen_slider", "label": "Slider plein écran", "twig": "_style_fullscreen_slider.html.twig" }
    ]
  },
  "key_figures": {
    "enabled": true,
    "styles": [
      { "key": "inline", "label": "En ligne", "twig": "_style_inline.html.twig", "default": true },
      { "key": "with_icons", "label": "Avec icônes", "twig": "_style_with_icons.html.twig" },
      { "key": "grid_2x2", "label": "Grille 2x2", "twig": "_style_grid_2x2.html.twig" }
    ]
  },
  "linked_pages": {
    "enabled": true,
    "styles": [
      { "key": "cards", "label": "Cartes", "twig": "_style_cards.html.twig", "default": true },
      { "key": "list", "label": "Liste", "twig": "_style_list.html.twig" },
      { "key": "horizontal", "label": "Horizontal", "twig": "_style_horizontal.html.twig" },
      { "key": "featured", "label": "Mis en avant", "twig": "_style_featured.html.twig" },
      { "key": "minimal", "label": "Minimal", "twig": "_style_minimal.html.twig" }
    ]
  },
  "location": {
    "enabled": true,
    "styles": [
      { "key": "map_only", "label": "Carte seule", "twig": "_style_map_only.html.twig", "default": true },
      { "key": "map_with_info", "label": "Carte + infos", "twig": "_style_map_with_info.html.twig" }
    ]
  },
  "form": {
    "enabled": true,
    "styles": [
      { "key": "centered", "label": "Centré", "twig": "_style_centered.html.twig", "default": true },
      { "key": "split", "label": "Avec image", "twig": "_style_split.html.twig" }
    ]
  },
  "document": {
    "enabled": true,
    "styles": [
      { "key": "default", "label": "Par défaut", "twig": "_style_default.html.twig", "default": true }
    ]
  }
}
```

---

## ADMIN SULU 3.0 — SPÉCIFICATION DÉTAILLÉE

### Navigation

Le thème doit apparaître dans le menu **Settings** (paramètres) de l'admin Sulu, PAS dans un menu racine custom. C'est un paramétrage du site, il doit être logiquement dans les Settings aux côtés des autres configurations.

Entrée de menu :
- **Parent** : Settings (sulu_admin.settings)
- **Nom** : "Thème" (clé de traduction : `iw_sulu_theme.themes`)
- **Icône** : `su-paint` ou icône pertinente disponible dans Sulu
- **Position** : après les entrées de base de Sulu

### Permissions (Security Context)

Le bundle doit déclarer son propre **security context** :

```
sulu.iw_sulu_theme.themes
```

Permissions requises : `view`, `add`, `edit`, `delete`

L'admin vérifie `PermissionTypes::VIEW` avant d'afficher la navigation et `PermissionTypes::EDIT` avant de permettre les modifications.

**Important** : la gestion des blocs dans les pages (choix du style, de la variante, des marges) fait partie de l'édition des pages Sulu. Elle est protégée par les permissions standards de page (`sulu.webspaces.<webspace>`). Pas besoin de permissions séparées pour ça.

### Vues Admin

**Vue liste** : `/admin/settings/themes`
- Liste des thèmes avec colonnes : Nom, Label, Actif (badge), Date de modification
- Actions : Ajouter, Supprimer
- Un seul thème actif à la fois (toggle)

**Vue formulaire** : `/admin/settings/themes/:id`
- Resource tab view avec les onglets suivants :

**Onglet "Général"** :
- Champ nom (text_line, obligatoire, unique)
- Champ label (text_line, obligatoire)
- Toggle "Thème actif"

**Onglet "Couleurs"** :
- 6 color pickers : primary, secondary, background, text, link, linkHover
- Chaque color picker = input color natif + champ texte hex
- Preview live des couleurs en pastilles dans l'interface

**Onglet "Typographie"** :
- Section "Familles de polices" :
  - Liste des familles chargées (heading, body, accent)
  - Pour chaque : preview texte, nom de la police, bouton "Changer"
  - Champ de recherche Google Fonts (autocomplete via API Google Fonts)
- Section "Assignations" :
  - Pour h1 à h6, body, link : sélection de la famille + poids + style

**Onglet "Boutons"** :
- Pour chaque variante de bouton (primary, secondary) :
  - 6 color pickers (bg, text, border, hoverBg, hoverText, hoverBorder)
  - Sélection du border-radius
  - Preview live du bouton avec état normal et hover

**Onglet "Bordures"** :
- Sélections pour : radius, radiusSm, radiusLg, imageRadius
- Preview visuel des arrondis

**Onglet "Variantes de blocs"** :
- **C'est le plus important pour l'UX**
- Liste des variantes existantes sous forme de cartes
- Chaque carte montre :
  - Un **preview live** : un mini-bloc avec titre, sous-titre, séparateur hr, paragraphe, lien, liste, le tout coloré avec les couleurs de la variante et sur le fond de la variante
  - En dessous du preview : 8 pastilles de couleur étiquetées (Fond, Titre, Sous-titre, Texte, Lien, Liste, Séparateur, Fond paragraphe)
  - Nom de la variante + boutons Modifier / Supprimer
- Bouton "Ajouter une variante"
- L'édition d'une variante affiche un formulaire avec :
  - Nom/label de la variante
  - 8 color pickers (title, subtitle, paragraph, link, list, hr, paragraphBg, blockBg)
  - Preview live qui se met à jour en temps réel
- **L'admin peut créer autant de variantes qu'il veut**

**Onglet "Menu"** :
- Sélection du type de menu : navbar, burger, fullscreen, sidebar
- Configuration des couleurs du menu (bg, text, hover, niveaux 2 et 3, etc.)
- Toggles : afficher logo desktop/mobile, afficher nom du site, afficher réseaux sociaux
- Nombre de niveaux enfants (select)

### Composants React Custom

**IMPORTANT** : Avant de créer un composant React custom, TOUJOURS vérifier si un composant existant dans `vendor/sulu/sulu/src/Sulu/Bundle/AdminBundle/Resources/js/` peut faire l'affaire. Utiliser au maximum les composants Sulu existants (Form, Input, Select, Checkbox, SingleSelect, ColorPicker si disponible, etc.). Ne créer un composant custom QUE si rien d'existant ne convient.

Composants custom probablement nécessaires :

1. **VariantPicker** (pour l'onglet "Variantes de blocs" dans la config admin ET pour le choix de variante dans les blocs de page)
   - Affiche les variantes sous forme de wireframes colorés
   - Chaque wireframe montre des barres de couleur représentant : titre (barre épaisse), sous-titre (barre moyenne), séparateur (ligne fine), paragraphe (barres fines), lien (barre colorée), liste (barres avec puces)
   - Les couleurs sont celles de la variante
   - Le fond du wireframe est la couleur blockBg de la variante
   - Au clic, sélectionne la variante
   - Pastilles de couleur avec tooltips en dessous

2. **StylePicker** (pour le choix du style d'agencement dans les blocs de page)
   - Affiche les styles disponibles pour le type de bloc courant sous forme de wireframes gris/violet
   - Chaque wireframe est un SVG simplifié montrant la disposition (texte à gauche + image à droite, texte centré, mosaïque, etc.)
   - Au clic, sélectionne le style
   - Label en dessous de chaque wireframe

3. **MarginSelector** (pour le choix des marges dans les blocs de page)
   - Grille de boutons avec les valeurs Tailwind : 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 20, 32
   - Le bouton sélectionné est mis en évidence
   - Compact et inline

4. **ColorTokenEditor** (pour l'édition des couleurs dans la config admin)
   - Input color natif + champ texte hex côte à côte
   - Preview de la couleur

5. **FontSelector** (pour la sélection des polices)
   - Champ autocomplete qui requête l'API Google Fonts
   - Preview de la police sélectionnée

### Enregistrement des composants React

Dans `assets/admin/index.js` :
```javascript
import { fieldRegistry } from 'sulu-admin-bundle/containers';

// Enregistrer les field types custom
fieldRegistry.add('iw_sulu_variant_picker', VariantPicker);
fieldRegistry.add('iw_sulu_style_picker', StylePicker);
fieldRegistry.add('iw_sulu_margin_selector', MarginSelector);
fieldRegistry.add('iw_sulu_color_token_editor', ColorTokenEditor);
fieldRegistry.add('iw_sulu_font_selector', FontSelector);
```

---

## BLOCS DE CONTENU — SPÉCIFICATION DÉTAILLÉE

### Architecture d'un bloc

Chaque bloc dans une page Sulu a **deux groupes de propriétés** :

**Propriétés de contenu** (spécifiques au type de bloc) :
- Titre, sous-titre, texte, images, liens, etc.

**Propriétés d'apparence** (communes à tous les blocs via des fragments XML) :
- `variant` (string) : clé de la variante de couleur (ex: "clair", "accent", "sombre")
- `style` (string) : clé du style d'agencement (ex: "classic", "overlay")
- `marginTop` (string) : classe Tailwind de marge haute (ex: "mt-5")
- `marginBottom` (string) : classe Tailwind de marge basse (ex: "mb-5")
- `lateralMargins` (boolean) : activer les marges latérales (container)
- `showBackground` (boolean) : appliquer la couleur de fond de la variante

### Définition XML des blocs

Utiliser des fragments XML réutilisables pour les propriétés communes :

**Fragment block_appearance.xml** (à inclure dans chaque bloc) :
```xml
<properties>
    <property name="variant" type="iw_sulu_variant_picker" mandatory="true">
        <meta>
            <title lang="fr">Variante de couleur</title>
            <title lang="en">Color variant</title>
        </meta>
    </property>
    <property name="style" type="iw_sulu_style_picker" mandatory="true">
        <meta>
            <title lang="fr">Style d'agencement</title>
            <title lang="en">Layout style</title>
        </meta>
        <params>
            <param name="block_type" value="__BLOCK_TYPE__"/>
        </params>
    </property>
    <property name="marginTop" type="iw_sulu_margin_selector">
        <meta>
            <title lang="fr">Marge haute</title>
        </meta>
    </property>
    <property name="marginBottom" type="iw_sulu_margin_selector">
        <meta>
            <title lang="fr">Marge basse</title>
        </meta>
    </property>
    <property name="lateralMargins" type="checkbox">
        <meta>
            <title lang="fr">Marges latérales</title>
        </meta>
    </property>
    <property name="showBackground" type="checkbox">
        <meta>
            <title lang="fr">Couleur de fond</title>
        </meta>
    </property>
</properties>
```

### Détail de chaque type de bloc

#### 1. Bloc `text`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `text` (text_editor) — Éditeur de texte riche

**Apparence** : fragment block_appearance (variant, style, margins, lateralMargins, showBackground)

**Styles disponibles** : centered, left_aligned, two_columns (3 fichiers Twig)

---

#### 2. Bloc `text_images`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `images` (media_selection) — Sélection d'images (si plusieurs → slider automatique)
- `imageFilter` (single_select) — Format d'image : 16:9, 4:3, 1:1, 3:4, original
- `imagePosition` (single_select) — Position : gauche, droite, dessus, arrière-plan
- `showImagesName` (checkbox) — Afficher le nom des images dans le fancybox
- `text` (text_editor) — Éditeur de texte riche

**Apparence** : fragment block_appearance

**Styles disponibles** : classic, overlay, fullwidth, mosaic, sidebar (5 fichiers Twig)

---

#### 3. Bloc `gallery`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `images` (media_selection) — Sélection d'images
- `showImagesName` (checkbox) — Afficher le nom des images dans le fancybox

**Apparence** : fragment block_appearance

**Styles disponibles** : grid, masonry, slider, carousel, lightbox_grid, fullscreen_slider (6 fichiers Twig)

---

#### 4. Bloc `key_figures`
**Contenu** :
- `figures` (block) — Bloc imbriqué, chaque figure contient :
  - `image` (single_media_selection) — Icône/image
  - `title` (text_line) — Label
  - `subTitle` (text_line) — Description
  - `number` (text_line) — Le chiffre/nombre

**Apparence** : fragment block_appearance (PAS de showBackground pour ce bloc — le fond dépend de la variante)

**Styles disponibles** : inline, with_icons, grid_2x2 (3 fichiers Twig)

---

#### 5. Bloc `linked_pages`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `links` (block) — Bloc imbriqué avec deux types :
  - Type `internal` : page_selection (sélection de page Sulu)
  - Type `external` : text_line (URL) + text_line (label) + text_line (ancre) + single_select (target: _self, _blank)

**Apparence** : fragment block_appearance

**Styles disponibles** : cards, list, horizontal, featured, minimal (5 fichiers Twig)

---

#### 6. Bloc `location`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `location` (location) — Type de contenu location Sulu (latitude, longitude, adresse)

**Apparence** : fragment block_appearance

**Styles disponibles** : map_only, map_with_info (2 fichiers Twig)

---

#### 7. Bloc `form`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `form` (single_form_selection) — Sélection d'un formulaire SuluFormBundle

**Apparence** : fragment block_appearance

**Styles disponibles** : centered, split (2 fichiers Twig)

---

#### 8. Bloc `document`
**Contenu** :
- `title` (text_line) — Titre
- `subTitle` (text_line) — Sous-titre
- `documents` (media_selection) — Sélection de médias de type document (PDF, etc.)

**Apparence** : fragment block_appearance

**Styles disponibles** : default (1 fichier Twig)

---

## FRONT-END — SPÉCIFICATION DÉTAILLÉE

### Stack technique (déjà en place dans le socle, ne pas reconfigurer)

- **Webpack Encore** : compilation des assets, déjà configuré dans `webpack.config.js`
- **Tailwind CSS 4** : classes utilitaires, déjà installé et configuré (approche CSS-first, pas de `tailwind.config.js`)
- **Stimulus** : controllers JS, bridge Symfony UX déjà en place

L'agent 3 doit utiliser ces outils sans les reconfigurer. Les entry points Webpack et la config Tailwind existent déjà.

### Principe fondamental

Le développeur front n'écrit **JAMAIS de couleur en dur** dans les templates Twig. Tout passe par :
1. Des **CSS custom properties** générées par le ThemeCompiler (pour les couleurs globales, typo, boutons, bordures)
2. Des **classes CSS générées** `.block-variant-*` (pour les variantes de blocs)
3. Des **classes utilitaires Tailwind CSS 4** pour le layout, les marges, le responsive (mais PAS pour les couleurs — celles-ci viennent des CSS variables du thème)

### ThemeCompiler — Génération du CSS

Le service `ThemeCompiler` lit le JSON `tokens` du thème actif et génère un fichier CSS :

**Fichier généré** : `public/build/theme-compiled.css` (ou un chemin cache)

**Contenu généré** :

```css
/* ===== GÉNÉRÉ PAR ThemeCompiler — NE PAS ÉDITER ===== */

/* Google Fonts imports */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=DM+Mono:wght@400&display=swap');

:root {
  /* Colors */
  --color-primary: #1a56db;
  --color-secondary: #7c3aed;
  --color-background: #ffffff;
  --color-text: #1f2937;
  --color-link: #1a56db;
  --color-link-hover: #1e40af;

  /* Typography */
  --font-heading: 'Inter', sans-serif;
  --font-body: 'Inter', sans-serif;
  --font-accent: 'DM Mono', monospace;
  --font-h1-weight: 700;
  --font-h2-weight: 600;
  /* ... etc pour chaque assignation ... */

  /* Borders */
  --radius: 8px;
  --radius-sm: 4px;
  --radius-lg: 16px;
  --radius-full: 9999px;
  --radius-img: 8px;

  /* Buttons - Primary */
  --btn-primary-bg: #1a56db;
  --btn-primary-text: #ffffff;
  --btn-primary-border: #1a56db;
  --btn-primary-hover-bg: #1544b8;
  --btn-primary-hover-text: #ffffff;
  --btn-primary-hover-border: #1544b8;
  --btn-primary-radius: 8px;

  /* Buttons - Secondary */
  --btn-secondary-bg: transparent;
  --btn-secondary-text: #1a56db;
  --btn-secondary-border: #1a56db;
  --btn-secondary-hover-bg: #1a56db;
  --btn-secondary-hover-text: #ffffff;
  --btn-secondary-hover-border: #1a56db;
  --btn-secondary-radius: 8px;

  /* Menu */
  --menu-bg: #ffffff;
  --menu-text: #1f2937;
  --menu-text-hover: #1a56db;
  /* ... etc ... */
}

/* ===== BLOCK VARIANTS (générées dynamiquement) ===== */

.block-variant-clair { background-color: #ffffff; }
.block-variant-clair h2, .block-variant-clair h3 { color: #1f2937; }
.block-variant-clair .block-subtitle { color: #4b5563; }
.block-variant-clair .block-text { color: #4b5563; }
.block-variant-clair .block-text a { color: #1a56db; }
.block-variant-clair .block-text ul, .block-variant-clair .block-text ol { color: #4b5563; }
.block-variant-clair hr { background-color: #e5e7eb; }
.block-variant-clair .block-paragraph-bg { background-color: transparent; }

.block-variant-accent { background-color: #1a56db; }
.block-variant-accent h2, .block-variant-accent h3 { color: #ffffff; }
.block-variant-accent .block-subtitle { color: #dbeafe; }
.block-variant-accent .block-text { color: #dbeafe; }
.block-variant-accent .block-text a { color: #ffffff; }
.block-variant-accent .block-text ul, .block-variant-accent .block-text ol { color: #dbeafe; }
.block-variant-accent hr { background-color: rgba(255,255,255,0.2); }
.block-variant-accent .block-paragraph-bg { background-color: rgba(255,255,255,0.08); }

/* ... etc pour chaque variante ... */
```

### Cache et invalidation

- Le CSS compilé est stocké dans le dossier `var/cache/iw_sulu_theme/`
- Un fichier `theme-{id}-{hash}.css` est généré (hash basé sur le updatedAt)
- Le `ThemeExtension` Twig expose le chemin du CSS compilé via `iw_sulu_theme_css_path()`
- Un EventSubscriber écoute le post-persist/post-update de ThemeConfig pour recompiler
- En production, le CSS n'est recompilé que si le hash change

### Templates Twig des blocs

**Fragment _block_wrapper.html.twig** (wrapper commun à tous les blocs) :

```twig
{# Wrapper appliqué à chaque bloc #}
{% set variantClass = 'block-variant-' ~ (variant|default('clair')) %}
{% set marginTopClass = marginTop|default('mt-0') %}
{% set marginBottomClass = marginBottom|default('mb-0') %}
{% set containerClass = lateralMargins|default(true) ? 'container mx-auto px-4 sm:px-6 lg:px-8' : '' %}

<section class="block {{ variantClass }} {{ marginTopClass }} {{ marginBottomClass }}"
         {% if showBackground|default(true) %}data-has-bg="true"{% endif %}>
    <div class="{{ containerClass }}">
        {% block content %}{% endblock %}
    </div>
</section>
```

**Fragment _titles.html.twig** :
```twig
{% if title is defined and title is not empty %}
    <h2 class="block-title">{{ title }}</h2>
{% endif %}
{% if subTitle is defined and subTitle is not empty %}
    <h3 class="block-subtitle">{{ subTitle }}</h3>
{% endif %}
{% if title is not empty or subTitle is not empty %}
    <hr>
{% endif %}
```

**Rendu des blocs (_blocks.html.twig)** :
```twig
{% for block in content.blocks %}
    {% set styleTwig = iw_sulu_block_style_template(block.type, block.style|default(null)) %}
    {% if styleTwig %}
        {% include '@ItechWorldSuluTheme/blocks/' ~ block.type ~ '/' ~ styleTwig with {
            title: block.title|default(''),
            subTitle: block.subTitle|default(''),
            variant: block.variant|default('clair'),
            style: block.style|default(null),
            marginTop: block.marginTop|default('mt-0'),
            marginBottom: block.marginBottom|default('mb-0'),
            lateralMargins: block.lateralMargins|default(true),
            showBackground: block.showBackground|default(true),
        } %}
    {% endif %}
{% endfor %}
```

### ThemeExtension Twig — Fonctions exposées

```php
class ThemeExtension extends AbstractExtension
{
    // Fonctions Twig :
    // - iw_sulu_theme_css_path() : string — chemin vers le CSS compilé du thème actif
    // - iw_sulu_theme_fonts_link() : string — balise <link> Google Fonts
    // - iw_sulu_block_style_template(blockType, styleKey) : ?string — nom du fichier twig pour un style donné
    // - iw_sulu_theme_menu_config() : array — configuration du menu actif
    // - iw_sulu_theme_tokens() : array — tokens bruts (pour usage avancé)

    // Variables globales Twig :
    // - iw_sulu_theme : array avec les tokens résolus du thème actif (cachés en mémoire)
}
```

---

## THÈMES PRÉDÉFINIS (Fixtures)

Créer 4 thèmes prédéfinis via les fixtures Doctrine :

1. **corporate** — "Corporate" : bleu marine, Inter, arrondis moyens, style professionnel
2. **creative** — "Créatif" : rose vif + ambre, Poppins/DM Sans, arrondis généreux
3. **minimal** — "Minimal" : noir et blanc, Playfair Display, aucun arrondi, épuré
4. **nature** — "Nature" : vert forêt + doré, DM Sans/Open Sans, arrondis prononcés

Chaque thème doit avoir au minimum 3 variantes de blocs (clair, accent, sombre) et tous les blockStyles configurés.

La commande `bin/console iw_sulu:theme:install corporate` doit charger le thème correspondant depuis les fixtures et le marquer comme actif.

---

## CONVENTIONS ET BONNES PRATIQUES

### Sulu 3.0.4 — Points critiques

**RÉFÉRENCE** : https://github.com/sulu/sulu/tree/3.0.4

1. **Plus de FOSRestBundle** : les routes API sont en YAML standard Symfony, pas de `type: rest`
2. **PHPCR réécrit** : le système de contenu a été entièrement réécrit, vérifier les interfaces actuelles dans le repo 3.0.4
3. **Validation des clés** : les clés de template et webspace doivent respecter `[a-z0-9_-]+` (max 31 chars)
4. **Extensions `.yaml`** : utiliser `.yaml` pas `.yml`
5. **Node 18+** requis pour le build JS admin
6. **ViewBuilder pattern** : utiliser les ViewBuilders (ListViewBuilder, FormViewBuilder, ResourceTabViewBuilder) pour déclarer les vues admin
7. **NavigationItemCollection** : utiliser `configureNavigationItems(NavigationItemCollection $navigationItemCollection)` pour le menu
8. **Security** : utiliser `SecurityChecker` et `SecurityCondition` pour les vérifications de permissions
9. **Vérifier les UPGRADE notes** : https://github.com/sulu/sulu/blob/3.0/UPGRADE-3.x.md pour les breaking changes vs Sulu 2.x

### Vérification systématique des vendors / du repo Sulu 3.0.4

Avant d'implémenter quoi que ce soit, **toujours regarder** :
- `vendor/sulu/sulu/src/Sulu/Bundle/AdminBundle/` — pour les patterns admin, composants React, registries
- `vendor/sulu/sulu/src/Sulu/Bundle/PageBundle/` — pour les content types, block handling
- `vendor/sulu/sulu/src/Sulu/Bundle/MediaBundle/` — pour la gestion des médias
- `vendor/sulu/sulu/src/Sulu/Bundle/SecurityBundle/` — pour les permissions

Reproduire les mêmes patterns : même structure de controller, même format de réponse API, même conventions de nommage.

### Code quality

- PHP 8.2+ avec types stricts, readonly properties où pertinent
- PHPStan level 6 minimum
- Tests unitaires pour ThemeCompiler et ThemeProvider
- Tests fonctionnels pour les controllers API
- PSR-12 pour le style de code
- Translations FR et EN pour toutes les clés admin

---

## RÉSUMÉ — CE QUE DOIT PRODUIRE CHAQUE AGENT

### Agent 1 (Backend)
- [ ] Entité ThemeConfig + migration Doctrine
- [ ] Repository ThemeConfigRepository
- [ ] Controller REST ThemeConfigController (CRUD complet)
- [ ] Service ThemeCompiler (JSON → CSS)
- [ ] Service ThemeProvider (fournit le thème actif, avec cache)
- [ ] Service GoogleFontsResolver
- [ ] EventSubscriber ThemeCompileSubscriber
- [ ] Commandes CLI (install, compile)
- [ ] Fixtures des 4 thèmes prédéfinis
- [ ] Configuration services.yaml et routes admin.yaml
- [ ] Classe bundle ItechWorldSuluThemeBundle.php
- [ ] Tests unitaires et fonctionnels

### Agent 2 (Admin Sulu)
- [ ] Classe ThemeAdmin (navigation, vues, security context)
- [ ] Tous les fichiers XML de formulaires (7 onglets)
- [ ] Fichier XML de liste des thèmes
- [ ] Composants React : VariantPicker, StylePicker, MarginSelector, ColorTokenEditor, FontSelector
- [ ] Fichier index.js avec enregistrement des composants
- [ ] Traductions FR et EN
- [ ] Vérification de cohérence avec les conventions Sulu 3.0

### Agent 3 (Front-end)
- [ ] Fichier CSS de base (theme-base.css) avec les classes utilitaires
- [ ] Template base.html.twig (layout de base avec inclusion du CSS compilé et Google Fonts)
- [ ] Tous les templates Twig de blocs (8 types × N styles = ~28 fichiers)
- [ ] Fragments communs (_block_wrapper, _titles, _paragraph, _image, _button)
- [ ] Templates de menu (4 types)
- [ ] Template de page par défaut (default.xml + default.html.twig)
- [ ] Controllers Stimulus JS (gallery, slider, location, form, key_figures animation)
- [ ] Définitions XML des block types (8 fichiers + fragments)
