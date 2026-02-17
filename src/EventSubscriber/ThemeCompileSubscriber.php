<?php

declare(strict_types=1);

namespace ItechWorld\SuluThemeBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use ItechWorld\SuluThemeBundle\Entity\ThemeConfig;
use ItechWorld\SuluThemeBundle\Service\ThemeCompiler;

/**
 * Doctrine listener that triggers theme CSS recompilation.
 *
 * Listens to postPersist and postUpdate events on ThemeConfig entities.
 * When an active theme is saved, its CSS is automatically recompiled
 * to reflect the latest token changes.
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class ThemeCompileSubscriber
{
    public function __construct(
        private readonly ThemeCompiler $compiler,
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
     * Only triggers recompilation for active themes.
     *
     * @param object $entity The Doctrine entity
     */
    private function handleEvent(object $entity): void
    {
        if (!$entity instanceof ThemeConfig) {
            return;
        }

        if ($entity->isActive()) {
            $this->compiler->compile($entity);
        }
    }
}
