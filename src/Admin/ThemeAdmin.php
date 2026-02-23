<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Admin;

use ItechWorld\SuluThemeBundle\Entity\ThemeConfig;
use ItechWorld\SuluThemeBundle\Repository\ThemeConfigRepository;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

/**
 * Registers the Sulu admin views, navigation items, and security contexts
 * for the Theme configuration management.
 *
 * Provides a full CRUD interface with separate form tabs for:
 * details, colors, typography, buttons, borders, variants, and menu.
 */
class ThemeAdmin extends Admin
{
    public const SECURITY_CONTEXT = 'sulu.iw_sulu_theme.themes';

    public const LIST_VIEW = 'iw_sulu_theme.list';

    public const ADD_FORM_VIEW = 'iw_sulu_theme.add_form';

    public const EDIT_FORM_VIEW = 'iw_sulu_theme.edit_form';

    /**
     * Section collapsibility configuration for block forms.
     *
     * Each entry maps a section name to its translation key and default open/closed state.
     * The JS module uses the translated label text to match sections in the DOM.
     *
     * @var array<string, array{translationKey: string, defaultOpen: bool}>
     */
    private const COLLAPSIBLE_SECTIONS = [
        'content' => ['translationKey' => 'iw_sulu_theme.content', 'defaultOpen' => true],
        'appearance' => ['translationKey' => 'iw_sulu_theme.appearance', 'defaultOpen' => false],
        'settings' => ['translationKey' => 'iw_sulu_theme.settings', 'defaultOpen' => false],
    ];

    /**
     * Available layout styles per block type, matching actual Twig templates
     * in templates/blocks/{type}/_style_{key}.html.twig.
     *
     * @var array<string, list<array{key: string, label: string}>>
     */
    private const BLOCK_STYLE_OPTIONS = [
        'text' => [
            ['key' => 'one_column', 'label' => 'iw_sulu_theme.style.one_column'],
            ['key' => 'two_columns', 'label' => 'iw_sulu_theme.style.two_columns'],
            ['key' => 'quote', 'label' => 'iw_sulu_theme.style.quote'],
        ],
        'text_images' => [
            ['key' => 'classic', 'label' => 'iw_sulu_theme.style.classic'],
            ['key' => 'overlay', 'label' => 'iw_sulu_theme.style.overlay'],
            ['key' => 'fullwidth', 'label' => 'iw_sulu_theme.style.fullwidth'],
            ['key' => 'mosaic', 'label' => 'iw_sulu_theme.style.mosaic'],
            ['key' => 'sidebar', 'label' => 'iw_sulu_theme.style.sidebar'],
            ['key' => 'hero_banner', 'label' => 'iw_sulu_theme.style.hero_banner'],
            ['key' => 'split_screen', 'label' => 'iw_sulu_theme.style.split_screen'],
        ],
        'gallery' => [
            ['key' => 'grid', 'label' => 'iw_sulu_theme.style.grid'],
            ['key' => 'masonry', 'label' => 'iw_sulu_theme.style.masonry'],
            ['key' => 'slider', 'label' => 'iw_sulu_theme.style.slider'],
            ['key' => 'carousel', 'label' => 'iw_sulu_theme.style.carousel'],
            ['key' => 'wide_carousel', 'label' => 'iw_sulu_theme.style.wide_carousel'],
            ['key' => 'filmstrip', 'label' => 'iw_sulu_theme.style.filmstrip'],
        ],
        'key_figures' => [
            ['key' => 'inline', 'label' => 'iw_sulu_theme.style.inline'],
            ['key' => 'with_icons', 'label' => 'iw_sulu_theme.style.with_icons'],
            ['key' => 'grid_2x2', 'label' => 'iw_sulu_theme.style.grid_2x2'],
            ['key' => 'progress', 'label' => 'iw_sulu_theme.style.progress'],
            ['key' => 'timeline', 'label' => 'iw_sulu_theme.style.timeline'],
        ],
        'linked_pages' => [
            ['key' => 'cards', 'label' => 'iw_sulu_theme.style.cards'],
            ['key' => 'list', 'label' => 'iw_sulu_theme.style.list'],
            ['key' => 'horizontal', 'label' => 'iw_sulu_theme.style.horizontal'],
            ['key' => 'featured', 'label' => 'iw_sulu_theme.style.featured'],
            ['key' => 'minimal', 'label' => 'iw_sulu_theme.style.minimal'],
            ['key' => 'image_cards', 'label' => 'iw_sulu_theme.style.image_cards'],
            ['key' => 'carousel', 'label' => 'iw_sulu_theme.style.carousel'],
        ],
        'location' => [
            ['key' => 'map_only', 'label' => 'iw_sulu_theme.style.map_only'],
            ['key' => 'map_with_info', 'label' => 'iw_sulu_theme.style.map_with_info'],
            ['key' => 'fullwidth', 'label' => 'iw_sulu_theme.style.fullwidth'],
            ['key' => 'overlay', 'label' => 'iw_sulu_theme.style.overlay'],
        ],
        'form' => [
            ['key' => 'centered', 'label' => 'iw_sulu_theme.style.centered'],
            ['key' => 'split', 'label' => 'iw_sulu_theme.style.split'],
            ['key' => 'card', 'label' => 'iw_sulu_theme.style.card'],
        ],
        'document' => [
            ['key' => 'default', 'label' => 'iw_sulu_theme.style.default'],
            ['key' => 'grid', 'label' => 'iw_sulu_theme.style.grid'],
            ['key' => 'accordion', 'label' => 'iw_sulu_theme.style.accordion'],
        ],
        'cta' => [
            ['key' => 'banner', 'label' => 'iw_sulu_theme.style.banner'],
            ['key' => 'centered', 'label' => 'iw_sulu_theme.style.centered'],
            ['key' => 'split', 'label' => 'iw_sulu_theme.style.split'],
            ['key' => 'floating', 'label' => 'iw_sulu_theme.style.floating'],
        ],
        'testimonial' => [
            ['key' => 'cards', 'label' => 'iw_sulu_theme.style.cards'],
            ['key' => 'slider', 'label' => 'iw_sulu_theme.style.slider'],
            ['key' => 'single', 'label' => 'iw_sulu_theme.style.single'],
            ['key' => 'minimal', 'label' => 'iw_sulu_theme.style.minimal'],
        ],
        'separator' => [
            ['key' => 'line', 'label' => 'iw_sulu_theme.style.line'],
            ['key' => 'spacer', 'label' => 'iw_sulu_theme.style.spacer'],
            ['key' => 'divider', 'label' => 'iw_sulu_theme.style.divider'],
        ],
    ];

    /**
     * @param ViewBuilderFactoryInterface $viewBuilderFactory The Sulu view builder factory
     * @param SecurityCheckerInterface    $securityChecker    The Sulu security checker
     * @param ThemeConfigRepository       $repository         The theme config repository
     */
    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker,
        private ThemeConfigRepository $repository,
    ) {
    }

    /**
     * Adds a "Themes" item in the Settings navigation menu.
     *
     * Only visible if the user has VIEW permission on the theme security context.
     *
     * @param NavigationItemCollection $navigationItemCollection The navigation collection
     */
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $themeItem = new NavigationItem('iw_sulu_theme.themes');
            $themeItem->setPosition(40);
            $themeItem->setIcon('su-paint');
            $themeItem->setView(static::LIST_VIEW);

            try {
                $navigationItemCollection->get(Admin::SETTINGS_NAVIGATION_ITEM)->addChild($themeItem);
            } catch (\Exception) {
                // Settings navigation item not available
            }
        }
    }

    /**
     * Configures the Sulu admin views for theme configuration management.
     *
     * Creates a list view, add form (with details tab only), and edit form
     * (with details, colors, typography, buttons, borders, variants, and menu tabs).
     *
     * @param ViewCollection $viewCollection The view collection to configure
     */
    public function configureViews(ViewCollection $viewCollection): void
    {
        $listToolbarActions = [];
        $formToolbarActions = [];

        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            // ── List view ──────────────────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createListViewBuilder(static::LIST_VIEW, '/themes')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setListKey(ThemeConfig::RESOURCE_KEY)
                    ->setTitle('iw_sulu_theme.themes')
                    ->addListAdapters(['table'])
                    ->setAddView(static::ADD_FORM_VIEW)
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($listToolbarActions)
            );

            // ── Add form (resource tab) ────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/themes/add')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setBackView(static::LIST_VIEW)
            );

            // ── Add form: details tab ──────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::ADD_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_details')
                    ->setTabTitle('iw_sulu_theme.details')
                    ->setEditView(static::EDIT_FORM_VIEW)
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::ADD_FORM_VIEW)
            );

            // ── Edit form (resource tab) ───────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/themes/:id')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setBackView(static::LIST_VIEW)
                    ->setTitleProperty('label')
            );

            // ── Edit form: details tab ─────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.details', '/details')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_details')
                    ->setTabTitle('iw_sulu_theme.details')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            // ── Edit form: colors tab ──────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.colors', '/colors')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_colors')
                    ->setTabTitle('iw_sulu_theme.colors')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            // ── Edit form: typography tab ──────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.typography', '/typography')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_typography')
                    ->setTabTitle('iw_sulu_theme.typography')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            // ── Edit form: buttons tab ─────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.buttons', '/buttons')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_buttons')
                    ->setTabTitle('iw_sulu_theme.buttons')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            // ── Edit form: borders tab ─────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.borders', '/borders')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_borders')
                    ->setTabTitle('iw_sulu_theme.borders')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            // ── Edit form: variants tab ────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.variants', '/variants')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_variants')
                    ->setTabTitle('iw_sulu_theme.variants')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            // ── Edit form: menu tab ────────────────────────────────
            $viewCollection->add(
                $this->viewBuilderFactory->createFormViewBuilder(static::EDIT_FORM_VIEW . '.menu', '/menu')
                    ->setResourceKey(ThemeConfig::RESOURCE_KEY)
                    ->setFormKey('iw_theme_config_menu')
                    ->setTabTitle('iw_sulu_theme.menu')
                    ->addToolbarActions($formToolbarActions)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
        }
    }

    /**
     * Returns the security contexts for the theme bundle.
     *
     * Registers a "Themes" permission under Settings, allowing
     * role-based access control on VIEW, ADD, EDIT, and DELETE operations.
     *
     * @return array<string, array<string, array<string, list<string>>>> Security contexts
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Settings' => [
                    static::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns the config key used by the JS initializer hook.
     *
     * Must match the first argument of initializer.addUpdateConfigHook() in index.js.
     *
     * @return string The config key
     */
    public function getConfigKey(): ?string
    {
        return 'iw_sulu_theme';
    }

    /**
     * Returns configuration data to be passed to the frontend JavaScript.
     *
     * Provides the active theme's block variants (for VariantPicker)
     * and the available layout styles per block type (for StylePicker).
     *
     * @return array<string, mixed>|null The config data
     */
    public function getConfig(): ?array
    {
        $variants = [];
        $activeTheme = $this->repository->findActive();

        if (null !== $activeTheme) {
            $tokens = $activeTheme->getTokens();
            $blockVariants = $tokens['blockVariants'] ?? [];

            foreach ($blockVariants as $key => $props) {
                if (!is_array($props)) {
                    continue;
                }

                $variants[] = array_merge(['key' => $key], $props);
            }
        }

        return [
            'variants' => $variants,
            'blockStyles' => self::BLOCK_STYLE_OPTIONS,
            'collapsibleSections' => self::COLLAPSIBLE_SECTIONS,
        ];
    }
}
