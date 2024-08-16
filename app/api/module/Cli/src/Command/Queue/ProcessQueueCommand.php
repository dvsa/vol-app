<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Cli\Service\Queue\QueueProcessor;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueueCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'queue:process';
    public const DEFAULT_RUN_FOR = 60;
    protected $sleepFor = 1000000;

    public function __construct(
        CommandHandlerManager $commandHandlerManager,
        private array $config,
        private readonly QueueProcessor $queueProcessor
    ) {
        parent::__construct(
            $commandHandlerManager
        );
    }

    /**
     * Setup command description and options
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Processes queue items.')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Queue message types to include, comma-separated.', '')
            ->addOption('exclude', null, InputOption::VALUE_OPTIONAL, 'Queue message types to exclude, comma-separated.', '')
            ->addOption('queue-duration', null, InputOption::VALUE_OPTIONAL, 'Duration to run the queue for, in seconds.', self::DEFAULT_RUN_FOR);
        parent::configure();
    }

    /**
     * Process the queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $includeTypes = array_filter(explode(',', (string) $input->getOption('type')));
        $excludeTypes = array_filter(explode(',', (string) $input->getOption('exclude')));
        $queueDuration = (int) $input->getOption('queue-duration') ?: $this->config['queue']['runFor'] ?? self::DEFAULT_RUN_FOR;

        $this->logAndWriteVerboseMessage("Processing queue items...");
        $this->logAndWriteVerboseMessage("Included types: " . implode(', ', $includeTypes));
        $this->logAndWriteVerboseMessage("Excluded types: " . implode(', ', $excludeTypes));
        $this->logAndWriteVerboseMessage("Processing duration: {$queueDuration} seconds");

        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $queueDuration) {
            try {
                $response = $this->queueProcessor->processNextItem($includeTypes, $excludeTypes);

                if ($response === null) {
                    usleep($this->sleepFor);
                } else {
                    $this->logAndWriteVerboseMessage("Processed: {$response}", \Laminas\Log\Logger::INFO);
                }
            } catch (Exception $e) {
                $this->logAndWriteVerboseMessage("ORM Error: {$e->getMessage()}", \Laminas\Log\Logger::ERR, true);
            }
        }

        $this->logAndWriteVerboseMessage("Queue processing completed.", \Laminas\Log\Logger::INFO);
        return Command::SUCCESS;
    }
}
