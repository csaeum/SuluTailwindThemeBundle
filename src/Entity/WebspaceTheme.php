<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ItechWorld\SuluTailwindThemeBundle\Repository\WebspaceThemeRepository;

/**
 * Junction entity mapping a webspace to its assigned theme.
 *
 * Each webspace can have at most one theme (unique constraint on webspaceKey).
 * Multiple webspaces can share the same ThemeConfig (many-to-one relationship).
 */
#[ORM\Entity(repositoryClass: WebspaceThemeRepository::class)]
#[ORM\Table(name: 'iw_sulu_tailwind_theme_webspace')]
class WebspaceTheme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * The Sulu webspace key (e.g. "website", "blog").
     */
    #[ORM\Column(type: Types::STRING, length: 64, unique: true)]
    private string $webspaceKey;

    /**
     * The theme assigned to this webspace.
     */
    #[ORM\ManyToOne(targetEntity: ThemeConfig::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ThemeConfig $theme;

    public function __construct(string $webspaceKey, ThemeConfig $theme)
    {
        $this->webspaceKey = $webspaceKey;
        $this->theme = $theme;
    }

    /**
     * Get the entity ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the webspace key.
     */
    public function getWebspaceKey(): string
    {
        return $this->webspaceKey;
    }

    /**
     * Set the webspace key.
     *
     * @return $this
     */
    public function setWebspaceKey(string $webspaceKey): static
    {
        $this->webspaceKey = $webspaceKey;

        return $this;
    }

    /**
     * Get the assigned theme.
     */
    public function getTheme(): ThemeConfig
    {
        return $this->theme;
    }

    /**
     * Set the assigned theme.
     *
     * @return $this
     */
    public function setTheme(ThemeConfig $theme): static
    {
        $this->theme = $theme;

        return $this;
    }
}
