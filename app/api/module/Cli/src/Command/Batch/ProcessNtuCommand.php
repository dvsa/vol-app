<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Query\Application\NotTakenUpList;
use Dvsa\Olcs\Transfer\Command\Application\NotTakenUpApplication;
use Olcs\Logging\Log\Logger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessNtuCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:process-ntu';

    #[\Override]
    protected function configure()
    {
        $this
            ->setDescription('Process Not Taken Up Applications.');
        $this->addCommonOptions();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $dryRun = $input->getOption('dry-run');
        $date = new \DateTime();

        $result = $this->handleQuery(NotTakenUpList::create(['date' => $date->format('Y-m-d')]));

        if (is_array($result) && isset($result['result'])) {
            $this->logAndWriteVerboseMessage(sprintf("<info>%d Application(s) found to change to NTU</info>", count($result['result'])));

            if (!$dryRun) {
                $failures = [];
                foreach ($result['result'] as $application) {
                    $this->logAndWriteVerboseMessage(sprintf("<comment>Processing Application ID %d</comment>", $application['id']));
                    try {
                        $commandResult = $this->commandHandlerManager->handleCommand(
                            NotTakenUpApplication::create(['id' => $application['id']])
                        );
                        foreach ($commandResult->getMessages() as $message) {
                            $this->logAndWriteVerboseMessage($message);
                        }
                    } catch (\Throwable $e) {
                        // Catch \Throwable, not \Exception: an Error from one malformed record
                        // must not abort the run for the applications after it
                        $failures[$application['id']] = sprintf(
                            '%s: %s (in %s:%d)',
                            $e::class,
                            $e->getMessage(),
                            $e->getFile(),
                            $e->getLine()
                        );
                        $this->logAndWriteVerboseMessage(
                            sprintf('Application %d failed: %s', $application['id'], $failures[$application['id']]),
                            LogLevel::ERROR,
                            true
                        );
                        Logger::log(
                            LogLevel::ERROR,
                            sprintf(
                                'Application %d NTU failure trace: %s',
                                $application['id'],
                                $e->getTraceAsString()
                            )
                        );
                    }
                }

                $summary = sprintf(
                    '%d of %d Application(s) processed to NTU',
                    count($result['result']) - count($failures),
                    count($result['result'])
                );
                if ($failures !== []) {
                    $summary .= sprintf(', failed Application IDs: %s', implode(', ', array_keys($failures)));
                }
                $this->logAndWriteVerboseMessage(
                    $summary,
                    $failures === [] ? LogLevel::INFO : LogLevel::ERROR,
                    $failures !== []
                );
                foreach ($failures as $id => $reason) {
                    $this->logAndWriteVerboseMessage(
                        sprintf('Application %d failed: %s', $id, $reason),
                        LogLevel::ERROR,
                        true
                    );
                }
            } else {
                $this->logAndWriteVerboseMessage("<comment>Dry run enabled. No changes made.</comment>");
            }

            return Command::SUCCESS;
        }

        $this->logAndWriteVerboseMessage("<error>No applications found to process.</error>");
        return Command::SUCCESS;
    }
}
