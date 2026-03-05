<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use ItechWorld\SuluTailwindThemeBundle\DataFixtures\ThemeFixtures;
use ItechWorld\SuluTailwindThemeBundle\Entity\ThemeConfig;
use ItechWorld\SuluTailwindThemeBundle\Repository\ThemeConfigRepository;
use ItechWorld\SuluTailwindThemeBundle\Service\ThemeCompiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to install a preset theme.
 *
 * Loads preset data from ThemeFixtures, creates the entity,
 * activates it, and compiles the CSS.
 */
#[AsCommand(
    name: 'iw-sulu:theme:install',
    description: 'Install a preset theme (corporate, creative, minimal, nature, halloween, christmas, megamenu)',
)]
class ThemeInstallCommand extends Command
{
    public function __construct(
        private readonly ThemeConfigRepository $repository,
        private readonly ThemeCompiler $compiler,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * Configure the command arguments.
     */
    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The preset theme name (corporate, creative, minimal, nature, halloween, christmas, megamenu)',
        );
    }

    /**
     * Execute the theme installation.
     *
     * @param InputInterface  $input  The console input
     * @param OutputInterface $output The console output
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $presets = ThemeFixtures::getPresets();

        if (!isset($presets[$name])) {
            $io->error(sprintf(
                'Unknown theme preset "%s". Available presets: %s',
                $name,
                implode(', ', array_keys($presets)),
            ));

            return Command::FAILURE;
        }

        // Check if theme already exists
        $existing = $this->repository->findByName($name);
        if (null !== $existing) {
            $io->warning(sprintf('Theme "%s" already exists (ID: %d). Updating...', $name, $existing->getId()));
            $theme = $existing;
        } else {
            $theme = new ThemeConfig();
            $io->info(sprintf('Creating new theme "%s"...', $name));
        }

        $preset = $presets[$name];

        $theme->setName($preset['name']);
        $theme->setLabel($preset['label']);
        $theme->setTokens($preset['tokens']);
        $theme->setMenuConfig($preset['menuConfig']);
        $theme->setBlockStyles($preset['blockStyles']);

        // Deactivate all themes and activate this one
        $this->repository->deactivateAll();
        $theme->setIsActive(true);

        $this->entityManager->persist($theme);
        $this->entityManager->flush();

        // Compile CSS
        $cssPath = $this->compiler->compile($theme);

        $io->success(sprintf(
            'Theme "%s" (%s) installed and activated successfully!',
            $theme->getLabel(),
            $theme->getName(),
        ));
        $io->table(
            ['Property', 'Value'],
            [
                ['ID', (string) $theme->getId()],
                ['Name', $theme->getName()],
                ['Label', $theme->getLabel()],
                ['Active', 'Yes'],
                ['CSS File', $cssPath],
                ['Colors', implode(', ', array_keys($theme->getTokens()['colors'] ?? []))],
                ['Font Families', (string) count($theme->getTokens()['typography']['families'] ?? [])],
                ['Block Variants', (string) count($theme->getTokens()['blockVariants'] ?? [])],
            ],
        );

        return Command::SUCCESS;
    }
}
