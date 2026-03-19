<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use ItechWorld\SuluTailwindThemeBundle\Entity\ThemeConfig;
use ItechWorld\SuluTailwindThemeBundle\Entity\WebspaceTheme;

/**
 * Repository for WebspaceTheme junction entities.
 *
 * Handles the mapping between Sulu webspace keys and ThemeConfig entities.
 *
 * @extends ServiceEntityRepository<WebspaceTheme>
 */
class WebspaceThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebspaceTheme::class);
    }

    /**
     * Find the theme assigned to a specific webspace.
     *
     * @param string $webspaceKey The Sulu webspace key
     *
     * @return ThemeConfig|null The assigned theme, or null if no theme is assigned
     */
    public function findThemeForWebspace(string $webspaceKey): ?ThemeConfig
    {
        $mapping = $this->findOneBy(['webspaceKey' => $webspaceKey]);

        return $mapping?->getTheme();
    }

    /**
     * Find all webspace mappings for a given theme entity.
     *
     * @param ThemeConfig $theme The theme to search for
     *
     * @return array<WebspaceTheme> The webspace theme mappings
     */
    public function findByTheme(ThemeConfig $theme): array
    {
        return $this->findBy(['theme' => $theme]);
    }

    /**
     * Find all webspace mappings for a given theme ID.
     *
     * Uses a DQL query to avoid loading the ThemeConfig entity,
     * which is more efficient for list enrichment in cgetAction().
     *
     * @param int $themeId The theme ID
     *
     * @return array<WebspaceTheme> The webspace theme mappings
     */
    public function findByThemeId(int $themeId): array
    {
        return $this->createQueryBuilder('wt')
            ->where('wt.theme = :themeId')
            ->setParameter('themeId', $themeId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Create or update the theme assignment for a webspace.
     *
     * Handles concurrent access via try/catch on UniqueConstraintViolationException:
     * if the insert fails because another process created the entry, retry with update.
     *
     * @param string      $webspaceKey The Sulu webspace key
     * @param ThemeConfig $theme       The theme to assign
     *
     * @return WebspaceTheme The created or updated mapping
     */
    public function setThemeForWebspace(string $webspaceKey, ThemeConfig $theme): WebspaceTheme
    {
        $existing = $this->findOneBy(['webspaceKey' => $webspaceKey]);

        if (null !== $existing) {
            $existing->setTheme($theme);

            return $existing;
        }

        $mapping = new WebspaceTheme($webspaceKey, $theme);

        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();

        try {
            $em->persist($mapping);
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            // Another process created the entry concurrently — update instead
            $em->clear(WebspaceTheme::class);
            $existing = $this->findOneBy(['webspaceKey' => $webspaceKey]);

            if (null !== $existing) {
                $existing->setTheme($theme);
                $em->flush();

                return $existing;
            }

            // Should not happen, but re-throw if it does
            throw new \RuntimeException(\sprintf('Failed to set theme for webspace "%s".', $webspaceKey));
        }

        return $mapping;
    }

    /**
     * Remove the theme assignment for a webspace.
     *
     * @param string $webspaceKey The Sulu webspace key
     */
    public function removeByWebspaceKey(string $webspaceKey): void
    {
        $mapping = $this->findOneBy(['webspaceKey' => $webspaceKey]);

        if (null !== $mapping) {
            $this->getEntityManager()->remove($mapping);
            $this->getEntityManager()->flush();
        }
    }
}
