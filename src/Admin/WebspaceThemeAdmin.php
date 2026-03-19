<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Page\Infrastructure\Sulu\Admin\PageAdmin;

/**
 * Adds a "Theme" tab to the webspace settings in the Sulu admin.
 *
 * Follows the SnippetAreaAdmin pattern: one tab per webspace under
 * PageAdmin::WEBSPACE_TABS_VIEW, with per-webspace security contexts.
 *
 * Uses a FormViewBuilder with setIdProperty('webspace') to map the
 * router attribute 'webspace' as the resource ID for the API call.
 *
 * NOTE: After updating the bundle, admin users must update their roles
 * to include the new per-webspace security contexts, otherwise this tab
 * will be invisible even to admins.
 */
class WebspaceThemeAdmin extends Admin
{
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly WebspaceManagerInterface $webspaceManager,
    ) {
    }

    /**
     * Build the per-webspace security context string.
     *
     * @param string $webspaceKey The webspace key (or '#webspace#' placeholder)
     *
     * @return string The security context identifier
     */
    public static function getSecurityContext(string $webspaceKey): string
    {
        return \sprintf('sulu.iw_sulu_tailwind_theme.%s.themes', $webspaceKey);
    }

    /**
     * Configure the "Theme" tab under webspace settings.
     *
     * Only visible if the user has VIEW permission on at least one webspace theme context.
     *
     * @param ViewCollection $viewCollection The view collection
     */
    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$this->hasSomeWebspaceThemePermission()) {
            return;
        }

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createViewBuilder(
                    'iw_sulu_tailwind_theme.webspace_theme',
                    '/theme',
                    'iw_sulu_tailwind_theme.webspace_theme_form'
                )
                ->setOption('tabTitle', 'iw_sulu_tailwind_theme.theme')
                ->setOption('tabOrder', 4096)
                ->setOption('resourceKey', 'iw_webspace_themes')
                ->setOption('formKey', 'iw_webspace_theme')
                ->setParent(PageAdmin::WEBSPACE_TABS_VIEW)
                ->addRerenderAttribute('webspace')
        );
    }

    /**
     * Returns per-webspace security contexts for role management.
     *
     * Each webspace gets its own security context with VIEW and EDIT permissions.
     *
     * @return array<string, array<string, array<string, list<string>>>> Security contexts
     */
    public function getSecurityContexts(): array
    {
        $webspaceContexts = [];

        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            $securityContextKey = self::getSecurityContext($webspace->getKey());
            $webspaceContexts[$securityContextKey] = self::getSecurityContextPermissions();
        }

        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Webspaces' => $webspaceContexts,
            ],
        ];
    }

    /**
     * Returns per-webspace security contexts with placeholder for dynamic resolution.
     *
     * @return array<string, array<string, array<string, list<string>>>> Security contexts
     */
    public function getSecurityContextsWithPlaceholder(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Webspaces' => [
                    self::getSecurityContext('#webspace#') => self::getSecurityContextPermissions(),
                ],
            ],
        ];
    }

    /**
     * Get the permission types for webspace theme contexts.
     *
     * @return list<string> The permission types
     */
    private static function getSecurityContextPermissions(): array
    {
        return [
            PermissionTypes::VIEW,
            PermissionTypes::EDIT,
        ];
    }

    /**
     * Check if the current user has VIEW permission on at least one webspace theme context.
     *
     * @return bool True if the user has permission on at least one webspace
     */
    private function hasSomeWebspaceThemePermission(): bool
    {
        foreach ($this->webspaceManager->getWebspaceCollection()->getWebspaces() as $webspace) {
            if ($this->securityChecker->hasPermission(
                self::getSecurityContext($webspace->getKey()),
                PermissionTypes::VIEW,
            )) {
                return true;
            }
        }

        return false;
    }
}
