<?php

declare(strict_types=1);

namespace ItechWorld\SuluTailwindThemeBundle\Command;

use ItechWorld\SuluTailwindThemeBundle\Service\GoogleFontsCatalog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to synchronize the Google Fonts catalog.
 *
 * Fetches the full font list from the Google Fonts API and caches it
 * locally for use by the FontPicker admin component.
 */
#[AsCommand(
    name: 'iw-sulu:theme:sync-fonts',
    description: 'Synchronize the Google Fonts catalog from the API',
)]
class FontsSyncCommand extends Command
{
    public function __construct(
        private readonly GoogleFontsCatalog $catalog,
    ) {
        parent::__construct();
    }

    /**
     * Execute the font catalog synchronization.
     *
     * @param InputInterface  $input  The console input
     * @param OutputInterface $output The console output
     *
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->catalog->hasApiKey()) {
            $io->error('Google Fonts API key is not configured.');
            $io->writeln('  Set <info>itech_world_sulu_tailwind_theme.google_fonts_api_key</info> in your bundle config:');
            $io->writeln('');
            $io->writeln('  <comment>itech_world_sulu_tailwind_theme:</comment>');
            $io->writeln('      <comment>google_fonts_api_key: \'%env(GOOGLE_FONTS_API_KEY)%\'</comment>');

            return Command::FAILURE;
        }

        $io->info('Synchronizing Google Fonts catalog...');

        try {
            $count = $this->catalog->sync();
            $io->success(sprintf('%d Google Fonts families synced successfully.', $count));
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
