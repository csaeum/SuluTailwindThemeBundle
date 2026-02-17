<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Admin;

use ItechWorld\SuluThemeBundle\Entity\ThemeConfig;
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
     * @param ViewBuilderFactoryInterface $viewBuilderFactory The Sulu view builder factory
     * @param SecurityCheckerInterface    $securityChecker    The Sulu security checker
     */
    public function __construct(
        private ViewBuilderFactoryInterface $viewBuilderFactory,
        private SecurityCheckerInterface $securityChecker,
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
}
