<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ItechWorld\SuluThemeBundle\Repository\ThemeConfigRepository;

/**
 * Represents a theme configuration with design tokens, menu config, and block styles.
 *
 * Design tokens are stored as JSON and compiled to CSS custom properties
 * by the ThemeCompiler service.
 */
#[ORM\Entity(repositoryClass: ThemeConfigRepository::class)]
#[ORM\Table(name: 'iw_sulu_theme_config')]
#[ORM\HasLifecycleCallbacks]
class ThemeConfig
{
    /**
     * Resource key used by Sulu admin for list/form resource registration.
     */
    public const RESOURCE_KEY = 'iw_theme_configs';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * Unique machine name for the theme (e.g. "corporate", "creative").
     */
    #[ORM\Column(type: Types::STRING, length: 64, unique: true)]
    private string $name = '';

    /**
     * Human-readable label for the theme.
     */
    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $label = '';

    /**
     * Design tokens (colors, typography, buttons, borders, blockVariants).
     *
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $tokens = [];

    /**
     * Menu configuration (layout, style, behavior).
     *
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $menuConfig = [];

    /**
     * Block styles configuration for each block type.
     *
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $blockStyles = [];

    /**
     * Whether this theme is currently active.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /**
     * ID of the user who created this theme (nullable for system-created themes).
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $createdBy = null;

    /**
     * ID of the user who last modified this theme.
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $changedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get the theme ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the unique machine name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the unique machine name.
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the human-readable label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Set the human-readable label.
     *
     * @return $this
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get all design tokens.
     *
     * @return array<string, mixed>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Set all design tokens.
     *
     * @param array<string, mixed> $tokens
     *
     * @return $this
     */
    public function setTokens(array $tokens): static
    {
        $this->tokens = $tokens;

        return $this;
    }

    /**
     * Get menu configuration.
     *
     * @return array<string, mixed>
     */
    public function getMenuConfig(): array
    {
        return $this->menuConfig;
    }

    /**
     * Set menu configuration.
     *
     * @param array<string, mixed> $menuConfig
     *
     * @return $this
     */
    public function setMenuConfig(array $menuConfig): static
    {
        $this->menuConfig = $menuConfig;

        return $this;
    }

    /**
     * Get block styles configuration.
     *
     * @return array<string, mixed>
     */
    public function getBlockStyles(): array
    {
        return $this->blockStyles;
    }

    /**
     * Set block styles configuration.
     *
     * @param array<string, mixed> $blockStyles
     *
     * @return $this
     */
    public function setBlockStyles(array $blockStyles): static
    {
        $this->blockStyles = $blockStyles;

        return $this;
    }

    /**
     * Check if this theme is currently active.
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Set the active state of this theme.
     *
     * @return $this
     */
    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get the creation timestamp.
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the last update timestamp.
     */
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update timestamp.
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get the ID of the user who created this theme.
     */
    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    /**
     * Set the ID of the user who created this theme.
     *
     * @return $this
     */
    public function setCreatedBy(?int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the ID of the user who last changed this theme.
     */
    public function getChangedBy(): ?int
    {
        return $this->changedBy;
    }

    /**
     * Set the ID of the user who last changed this theme.
     *
     * @return $this
     */
    public function setChangedBy(?int $changedBy): static
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    /**
     * Lifecycle callback to set timestamps on initial persist.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Lifecycle callback to update the updatedAt timestamp before each update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
