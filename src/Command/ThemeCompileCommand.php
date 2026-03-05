<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Command;

use ItechWorld\SuluTailwindThemeBundle\Repository\ThemeConfigRepository;
use ItechWorld\SuluTailwindThemeBundle\Service\ThemeCompiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to compile a theme's CSS from its design tokens.
 *
 * Can compile a specific theme by name or the currently active theme.
 */
#[AsCommand(
    name: 'iw-sulu:theme:compile',
    description: 'Compile theme CSS from design tokens',
)]
class ThemeCompileCommand extends Command
{
    public function __construct(
        private readonly ThemeConfigRepository $repository,
        private readonly ThemeCompiler $compiler,
    ) {
        parent::__construct();
    }

    /**
     * Configure the command options.
     */
    protected function configure(): void
    {
        $this->addOption(
            'theme',
            't',
            InputOption::VALUE_OPTIONAL,
            'The theme name to compile (defaults to the active theme)',
        );
    }

    /**
     * Execute the theme compilation.
     *
     * @param InputInterface  $input  The console input
     * @param OutputInterface $output The console output
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $themeName = $input->getOption('theme');

        if (null !== $themeName) {
            $theme = $this->repository->findByName($themeName);

            if (null === $theme) {
                $io->error(sprintf('Theme "%s" not found.', $themeName));

                return Command::FAILURE;
            }
        } else {
            $theme = $this->repository->findActive();

            if (null === $theme) {
                $io->error('No active theme found. Specify a theme name with --theme.');

                return Command::FAILURE;
            }
        }

        $io->info(sprintf('Compiling theme "%s" (%s)...', $theme->getLabel(), $theme->getName()));

        $cssPath = $this->compiler->compile($theme);

        $io->success(sprintf('Theme compiled successfully!'));
        $io->writeln(sprintf('  CSS file: <info>%s</info>', $cssPath));
        $io->writeln(sprintf('  Web path: <info>%s</info>', $this->compiler->getCssPath($theme)));

        return Command::SUCCESS;
    }
}
