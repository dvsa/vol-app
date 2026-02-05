<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\CacheClear;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Cache Clear Command
 *
 * Clears Redis cache with options for full flush or selective namespace/pattern-based clearing.
 * This is designed for containerized environments where Redis is the shared cache across all ECS tasks.
 */
class CacheClearCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:cache-clear';

    /**
     * Available cache namespaces from CacheEncryption service
     */
    private const array NAMESPACES = [
        'user_account',
        'sys_param',
        'sys_param_list',
        'translation_key',
        'translation_replacement',
        'storage',
        'secretsmanager',
    ];

    protected function configure()
    {
        $this
            ->setDescription('Clear Redis cache (full flush or selective by namespace/pattern)')
            ->addOption(
                'flush-all',
                null,
                InputOption::VALUE_NONE,
                'Flush all Redis cache data (FLUSHDB)'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Clear specific cache namespace(s) (comma-separated). Available: ' . implode(', ', self::NAMESPACES)
            )
            ->addOption(
                'pattern',
                null,
                InputOption::VALUE_REQUIRED,
                'Clear cache keys matching this pattern (e.g., "zfcache:user_account*")'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Skip confirmation prompts'
            );

        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $flushAll = $input->getOption('flush-all');
        $namespace = $input->getOption('namespace');
        $pattern = $input->getOption('pattern');
        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');

        // Validate options
        if (!$flushAll && !$namespace && !$pattern) {
            $output->writeln('<error>You must specify one of: --flush-all, --namespace, or --pattern</error>');
            return self::FAILURE;
        }

        if ($flushAll && ($namespace || $pattern)) {
            $output->writeln('<error>Cannot use --flush-all with --namespace or --pattern</error>');
            return self::FAILURE;
        }

        // Validate namespaces if provided
        if ($namespace) {
            $namespaces = array_map(trim(...), explode(',', (string) $namespace));
            $invalid = array_diff($namespaces, self::NAMESPACES);
            if (!empty($invalid)) {
                $output->writeln(sprintf(
                    '<error>Invalid namespace(s): %s. Available: %s</error>',
                    implode(', ', $invalid),
                    implode(', ', self::NAMESPACES)
                ));
                return self::FAILURE;
            }
        }

        // Confirmation for full flush (unless forced or dry-run)
        if ($flushAll && !$force && !$dryRun) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<question>Are you sure you want to FLUSH ALL Redis cache? This cannot be undone. (y/N)</question> ',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<comment>Cache clear cancelled.</comment>');
                return self::SUCCESS;
            }
        }

        // Build command DTO
        $command = CacheClear::create([
            'flushAll' => $flushAll,
            'namespace' => $namespace,
            'pattern' => $pattern,
            'dryRun' => $dryRun,
        ]);

        // Execute via command handler
        $result = $this->handleCommand([$command]);

        if ($dryRun) {
            return $this->outputResult(
                $result,
                'Dry run completed - no cache was actually cleared',
                'Dry run failed'
            );
        }

        return $this->outputResult(
            $result,
            'Cache cleared successfully',
            'Failed to clear cache'
        );
    }
}
