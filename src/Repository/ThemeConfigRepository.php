<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ItechWorld\SuluThemeBundle\Entity\ThemeConfig;

/**
 * Repository for ThemeConfig entities.
 *
 * Provides helper methods for querying theme configurations,
 * including finding the active theme and deactivating all themes.
 *
 * @extends ServiceEntityRepository<ThemeConfig>
 */
class ThemeConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeConfig::class);
    }

    /**
     * Find the currently active theme configuration.
     *
     * @return ThemeConfig|null The active theme, or null if none is active
     */
    public function findActive(): ?ThemeConfig
    {
        return $this->findOneBy(['isActive' => true]);
    }

    /**
     * Find a theme configuration by its unique machine name.
     *
     * @param string $name The theme machine name
     *
     * @return ThemeConfig|null The matching theme, or null if not found
     */
    public function findByName(string $name): ?ThemeConfig
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Deactivate all theme configurations using a single DQL update.
     *
     * This should be called before activating a new theme to ensure
     * only one theme is active at a time.
     */
    public function deactivateAll(): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.isActive', ':inactive')
            ->setParameter('inactive', false)
            ->getQuery()
            ->execute();
    }
}
