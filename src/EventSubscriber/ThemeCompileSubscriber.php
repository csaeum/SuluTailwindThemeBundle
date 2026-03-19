<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use ItechWorld\SuluTailwindThemeBundle\Entity\ThemeConfig;
use ItechWorld\SuluTailwindThemeBundle\Repository\WebspaceThemeRepository;
use ItechWorld\SuluTailwindThemeBundle\Service\ThemeCompiler;

/**
 * Doctrine listener that triggers theme CSS recompilation.
 *
 * Listens to postPersist and postUpdate events on ThemeConfig entities.
 * When a theme assigned to at least one webspace is saved, its CSS is
 * automatically recompiled to reflect the latest token changes.
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class ThemeCompileSubscriber
{
    public function __construct(
        private readonly ThemeCompiler $compiler,
        private readonly WebspaceThemeRepository $webspaceThemeRepository,
    ) {
    }

    /**
     * Handle the postPersist event for ThemeConfig entities.
     *
     * @param PostPersistEventArgs $args The event arguments
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->handleEvent($args->getObject());
    }

    /**
     * Handle the postUpdate event for ThemeConfig entities.
     *
     * @param PostUpdateEventArgs $args The event arguments
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->handleEvent($args->getObject());
    }

    /**
     * Process a ThemeConfig entity event.
     *
     * Only triggers recompilation for themes assigned to at least one webspace.
     *
     * @param object $entity The Doctrine entity
     */
    private function handleEvent(object $entity): void
    {
        if (!$entity instanceof ThemeConfig) {
            return;
        }

        if (count($this->webspaceThemeRepository->findByTheme($entity)) > 0) {
            $this->compiler->compile($entity);
        }
    }
}
