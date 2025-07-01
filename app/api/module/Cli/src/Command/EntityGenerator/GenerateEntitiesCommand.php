<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\EntityGenerator;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Domain\Command\EntityGenerator\GenerateEntities as GenerateEntitiesDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generate entities CLI command
 */
class GenerateEntitiesCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'entity:generate';
    protected static $defaultDescription = 'Generate Doctrine entities from database schema';

    public function __construct(CommandHandlerManager $commandHandlerManager)
    {
        parent::__construct($commandHandlerManager);
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription)
            ->addOption(
                'output-path',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output path for generated entities (default: /tmp/generated-entities)'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Show what would be generated without writing files'
            )
            ->addOption(
                'replace',
                'r',
                InputOption::VALUE_NONE,
                'Replace existing entities (use with caution!)'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Path to EntityConfig.php file (default: data/db/EntityConfig.php)'
            )
            ->addOption(
                'include-tables',
                'i',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of tables to include (if not specified, all tables are included)'
            )
            ->addOption(
                'exclude-tables',
                'e',
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of tables to exclude'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('OLCS Entity Generator');

        // Parse table lists
        $includeTables = $this->parseTableList($input->getOption('include-tables'));
        $excludeTables = $this->parseTableList($input->getOption('exclude-tables'));

        // Debug output
        $io->note([
            'Raw include-tables option: ' . var_export($input->getOption('include-tables'), true),
            'Raw exclude-tables option: ' . var_export($input->getOption('exclude-tables'), true),
            'Raw output-path option: ' . var_export($input->getOption('output-path'), true),
            'Parsed include tables: ' . implode(', ', $includeTables),
            'Parsed exclude tables: ' . implode(', ', $excludeTables),
        ]);

        $command = new GenerateEntitiesDto([
            'outputPath' => $input->getOption('output-path'),
            'dryRun' => $input->getOption('dry-run'),
            'replace' => $input->getOption('replace'),
            'configPath' => $input->getOption('config'),
            'includeTables' => $includeTables,
            'excludeTables' => $excludeTables,
        ]);

        // Warn if replace mode is enabled
        if ($command->isReplace() && !$command->isDryRun()) {
            if (!$io->confirm('You are about to replace existing entities. This cannot be undone. Continue?', false)) {
                $io->warning('Operation cancelled.');
                return Command::FAILURE;
            }
        }

        try {
            $result = $this->commandHandlerManager->handleCommand($command);

            $this->displayResult($io, $result);

            return $result->getFlag('success') ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $io->error([
                'Entity generation failed:',
                $e->getMessage()
            ]);

            if ($output->isVerbose()) {
                $io->text($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Parse comma-separated table list
     */
    private function parseTableList(?string $tableList): array
    {
        if (empty($tableList)) {
            return [];
        }

        return array_map('trim', explode(',', $tableList));
    }

    /**
     * Display generation result
     */
    private function displayResult(SymfonyStyle $io, \Dvsa\Olcs\Api\Domain\Command\Result $result): void
    {
        // Display messages from the command handler
        foreach ($result->getMessages() as $message) {
            $io->text($message);
        }

        if ($result->getFlag('dryRun')) {
            $io->info('DRY RUN - No files were written');
        }

        if ($result->getFlag('success')) {
            $io->success(sprintf(
                'Generated %d entities in %.2f seconds',
                $result->getFlag('entityCount'),
                $result->getFlag('duration')
            ));
        }

        $warnings = $result->getFlag('warnings') ?? [];
        if (!empty($warnings)) {
            $io->warning('Warnings:');
            foreach ($warnings as $warning) {
                $io->text('  • ' . $warning);
            }
        }

        $errors = $result->getFlag('errors') ?? [];
        if (!empty($errors)) {
            $io->error('Errors:');
            foreach ($errors as $error) {
                $io->text('  • ' . $error);
            }
        }

        if (!$result->getFlag('dryRun') && $result->getFlag('success')) {
            $io->note([
                'Entities have been generated. Next steps:',
                '1. Review the generated entities in: ' . ($result->getFlag('outputPath') ?? '/tmp/generated-entities'),
                '2. Compare with existing entities if needed',
                '3. Run tests to ensure compatibility',
            ]);
        }
    }
}