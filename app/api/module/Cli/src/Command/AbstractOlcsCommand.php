<?php

namespace Dvsa\Olcs\Cli\Command;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractOlcsCommand extends Command
{
    protected OutputInterface $output;

    public function __construct(
        protected CommandHandlerManager $commandHandlerManager
    ) {
        parent::__construct();
    }

    protected function initializeOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Handle an array of command DTOs
     *
     * @return int
     */
    protected function handleCommand(array $dto): int
    {
        try {
            foreach ($dto as $count => $dtoCommand) {
                $logMessage = "Handling command " . ($count + 1) . ': ' . $dtoCommand::class;
                $this->logAndWriteVerboseMessage($logMessage);

                if ($this->isVerbose()) {
                    $this->output->writeln($logMessage);
                }

                $result = $this->commandHandlerManager->handleCommand($dtoCommand);
                foreach ($result->getMessages() as $message) {
                    $this->logAndWriteVerboseMessage($message);
                }
            }
        } catch (NotFoundException $e) {
            $this->logAndWriteVerboseMessage('NotFoundException: ' . $e->getMessage(), \Laminas\Log\Logger::WARN, true);
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->logAndWriteVerboseMessage('Error: ' . $e->getMessage(), \Laminas\Log\Logger::ERR, true);
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    /**
     * Log messages, output to console if verbose mode is enabled.
     *
     * @return void
     */
    protected function logAndWriteVerboseMessage(string $message, int $logPriority = \Laminas\Log\Logger::DEBUG, bool $isError = false)
    {
        Logger::log($logPriority, $message);
        $formattedMessage = $isError ? "<error>$message</error>" : "<info>$message</info>";

        if ($this->output->isVerbose()) {
            $this->output->writeln($formattedMessage);
        }
    }

    /**
     * Has user requested verbose output
     *
     * @return bool
     */
    protected function isVerbose(): bool
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }


    /**
     * Has user requested dry run
     *
     * @return bool
     */
    protected function isDryRun(InputInterface $input): bool
    {
        return $input->getOption('dry-run');
    }

    /**
     * Output the result of an operation with the appropriate message.
     *
     * @return int The status code
     */
    protected function outputResult(int $result, string $successMessage, string $failureMessage): int
    {
        if ($result === 0) {
            $this->logAndWriteVerboseMessage($successMessage);
            return Command::SUCCESS;
        } else {
            $this->logAndWriteVerboseMessage($failureMessage, \Laminas\Log\Logger::ERR, true);
            return Command::FAILURE;
        }
    }
}
