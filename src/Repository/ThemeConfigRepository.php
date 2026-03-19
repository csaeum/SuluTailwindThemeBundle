<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ItechWorld\SuluTailwindThemeBundle\Entity\ThemeConfig;

/**
 * Repository for ThemeConfig entities.
 *
 * Provides helper methods for querying theme configurations.
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
}
