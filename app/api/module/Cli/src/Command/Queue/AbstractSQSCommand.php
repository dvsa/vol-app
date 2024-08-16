<?php

namespace Dvsa\Olcs\Cli\Command\Queue;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSQSCommand extends AbstractOlcsCommand
{
    public function __construct(
        CommandHandlerManager $commandHandlerManager
    ) {
        parent::__construct(
            $commandHandlerManager
        );
    }

    protected function configure()
    {
        $this
            ->addOption(
                'queue-duration',
                null,
                InputOption::VALUE_OPTIONAL,
                'Duration to run the queue processor, in seconds.',
                60
            );
    }

    /**
     * execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $queueDuration = (int) $input->getOption('queue-duration');
        $this->logAndWriteVerboseMessage("Queue duration: {$queueDuration} seconds");

        $startTime = microtime(true);
        $endTime = $startTime + $queueDuration;

        while (microtime(true) < $endTime) {
            try {
                $dto = $this->getCommandDto();
                $result = $this->commandHandlerManager->handleCommand($dto);

                foreach ($result->getMessages() as $message) {
                    $this->logAndWriteVerboseMessage("Processed: $message");
                }
            } catch (Exception $e) {
                $this->logAndWriteVerboseMessage("Error processing queue: {$e->getMessage()}", \Laminas\Log\Logger::ERR, true);
                continue;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Get the dto for the command
     *
     * @return mixed The DTO to be processed.
     */
    abstract protected function getCommandDto();
}
