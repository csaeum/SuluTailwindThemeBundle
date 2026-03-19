<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use ItechWorld\SuluTailwindThemeBundle\Repository\ThemeConfigRepository;
use ItechWorld\SuluTailwindThemeBundle\Repository\WebspaceThemeRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One-time migration command for upgrading from isActive to multi-webspace theme support.
 *
 * This command:
 * 1. Checks if the old `is_active` column exists on the theme config table
 * 2. Reads the previously active theme via raw SQL (since the entity no longer has the field)
 * 3. Creates the webspace theme table if it doesn't exist
 * 4. Creates WebspaceTheme entries for every webspace, assigning the old active theme
 *
 * Safe to run multiple times (idempotent). Must be run BEFORE `doctrine:schema:update`
 * so the old `is_active` column can still be read.
 *
 * Usage:
 *   php bin/adminconsole iw-sulu:theme:migrate-webspaces
 */
#[AsCommand(
    name: 'iw-sulu:theme:migrate-webspaces',
    description: 'Migrate from single active theme to per-webspace theme assignment',
)]
class ThemeMigrateWebspacesCommand extends Command
{
    private const CONFIG_TABLE = 'iw_sulu_tailwind_theme_config';
    private const WEBSPACE_TABLE = 'iw_sulu_tailwind_theme_webspace';

    public function __construct(
        private readonly Connection $connection,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly WebspaceThemeRepository $webspaceThemeRepository,
        private readonly ThemeConfigRepository $themeConfigRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * Execute the migration.
     *
     * @param InputInterface  $input  The console input
     * @param OutputInterface $output The console output
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if WebspaceTheme entries already exist (already migrated)
        if ($this->webspaceTableExists() && count($this->webspaceThemeRepository->findAll()) > 0) {
            $io->success('Nothing to migrate — webspace theme assignments already exist.');

            return Command::SUCCESS;
        }

        // Check if the old is_active column exists
        $hasIsActiveColumn = $this->hasIsActiveColumn();

        if (!$hasIsActiveColumn) {
            $io->success('Nothing to migrate — the is_active column does not exist (already removed).');

            return Command::SUCCESS;
        }

        // Read the old active theme ID via raw SQL
        $activeThemeId = $this->connection->fetchOne(
            'SELECT id FROM ' . self::CONFIG_TABLE . ' WHERE is_active = 1 LIMIT 1',
        );

        if (false === $activeThemeId || null === $activeThemeId) {
            $io->warning('No active theme found in the old is_active column. Nothing to migrate.');

            return Command::SUCCESS;
        }

        $activeThemeId = (int) $activeThemeId;
        $activeTheme = $this->themeConfigRepository->find($activeThemeId);

        if (null === $activeTheme) {
            $io->error(\sprintf('Theme with ID %d not found in the repository.', $activeThemeId));

            return Command::FAILURE;
        }

        // Create the webspace theme table if it doesn't exist yet
        if (!$this->webspaceTableExists()) {
            $this->createWebspaceTable();
            $io->info('Created table ' . self::WEBSPACE_TABLE);
        }

        // Assign the old active theme to all webspaces
        $webspaces = $this->webspaceManager->getWebspaceCollection()->getWebspaces();
        $count = 0;

        foreach ($webspaces as $webspace) {
            $webspaceKey = $webspace->getKey();

            // Skip if already assigned
            $existing = $this->webspaceThemeRepository->findThemeForWebspace($webspaceKey);
            if (null !== $existing) {
                $io->note(\sprintf('Webspace "%s" already has a theme assigned. Skipping.', $webspaceKey));
                continue;
            }

            $this->webspaceThemeRepository->setThemeForWebspace($webspaceKey, $activeTheme);
            ++$count;
            $io->info(\sprintf('Assigned theme "%s" to webspace "%s"', $activeTheme->getLabel(), $webspaceKey));
        }

        $this->entityManager->flush();

        $io->success(\sprintf(
            'Migration complete! Theme "%s" (ID: %d) assigned to %d webspace(s).',
            $activeTheme->getLabel(),
            $activeThemeId,
            $count,
        ));

        $io->note([
            'Next steps:',
            '1. Run "php bin/adminconsole doctrine:schema:update --force" to finalize the schema',
            '2. Update admin roles to include per-webspace theme security contexts',
            '3. Clear the cache: "php bin/adminconsole cache:clear"',
        ]);

        return Command::SUCCESS;
    }

    /**
     * Check if the old is_active column exists on the config table.
     */
    private function hasIsActiveColumn(): bool
    {
        /** @var AbstractSchemaManager<\Doctrine\DBAL\Platforms\AbstractPlatform> $schemaManager */
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist([self::CONFIG_TABLE])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns(self::CONFIG_TABLE);

        return isset($columns['is_active']);
    }

    /**
     * Check if the webspace theme table already exists.
     */
    private function webspaceTableExists(): bool
    {
        /** @var AbstractSchemaManager<\Doctrine\DBAL\Platforms\AbstractPlatform> $schemaManager */
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist([self::WEBSPACE_TABLE]);
    }

    /**
     * Create the webspace theme table via DBAL.
     *
     * This makes the command self-contained — it doesn't rely on a prior
     * doctrine:schema:update to create the table.
     */
    private function createWebspaceTable(): void
    {
        $this->connection->executeStatement(\sprintf(
            'CREATE TABLE %s (
                id INT AUTO_INCREMENT NOT NULL,
                theme_id INT NOT NULL,
                webspace_key VARCHAR(64) NOT NULL,
                UNIQUE INDEX UNIQ_webspace_key (webspace_key),
                INDEX IDX_theme_id (theme_id),
                CONSTRAINT FK_theme_id FOREIGN KEY (theme_id) REFERENCES %s (id) ON DELETE CASCADE,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB',
            self::WEBSPACE_TABLE,
            self::CONFIG_TABLE,
        ));
    }
}
