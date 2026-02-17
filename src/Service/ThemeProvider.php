<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Service;

use ItechWorld\SuluThemeBundle\Entity\ThemeConfig;
use ItechWorld\SuluThemeBundle\Repository\ThemeConfigRepository;

/**
 * Provides access to the currently active theme configuration.
 *
 * Uses an in-memory cache to avoid repeated database queries within
 * a single request. Delegates CSS compilation to the ThemeCompiler service.
 */
class ThemeProvider
{
    /**
     * In-memory cache for the active theme (false = not loaded yet).
     */
    private ThemeConfig|null|false $activeThemeCache = false;

    public function __construct(
        private readonly ThemeConfigRepository $repository,
        private readonly ThemeCompiler $compiler,
    ) {
    }

    /**
     * Get the currently active theme configuration.
     *
     * Uses an in-memory cache to avoid repeated database queries
     * within the same request lifecycle.
     *
     * @return ThemeConfig|null The active theme, or null if none is active
     */
    public function getActiveTheme(): ?ThemeConfig
    {
        if (false === $this->activeThemeCache) {
            $this->activeThemeCache = $this->repository->findActive();
        }

        return $this->activeThemeCache;
    }

    /**
     * Get the web-accessible CSS path for the active theme.
     *
     * @return string|null The CSS path, or null if no theme is active
     */
    public function getCssPath(): ?string
    {
        $theme = $this->getActiveTheme();

        if (null === $theme) {
            return null;
        }

        return $this->compiler->getCssPath($theme);
    }

    /**
     * Get the design tokens for the active theme.
     *
     * @return array<string, mixed> The tokens array, or empty array if no theme is active
     */
    public function getTokens(): array
    {
        $theme = $this->getActiveTheme();

        if (null === $theme) {
            return [];
        }

        return $theme->getTokens();
    }

    /**
     * Get the menu configuration for the active theme.
     *
     * @return array<string, mixed> The menu config, or empty array if no theme is active
     */
    public function getMenuConfig(): array
    {
        $theme = $this->getActiveTheme();

        if (null === $theme) {
            return [];
        }

        return $theme->getMenuConfig();
    }

    /**
     * Get the block styles for the active theme.
     *
     * @return array<string, mixed> The block styles, or empty array if no theme is active
     */
    public function getBlockStyles(): array
    {
        $theme = $this->getActiveTheme();

        if (null === $theme) {
            return [];
        }

        return $theme->getBlockStyles();
    }

    /**
     * Reset the in-memory cache.
     *
     * Should be called after theme activation changes within the same request.
     */
    public function resetCache(): void
    {
        $this->activeThemeCache = false;
    }
}
