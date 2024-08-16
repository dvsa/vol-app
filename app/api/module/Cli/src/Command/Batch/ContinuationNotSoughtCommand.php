<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Licence\EnqueueContinuationNotSought;
use Dvsa\Olcs\Api\Domain\Query\Licence\ContinuationNotSoughtList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ContinuationNotSoughtCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:continuation-not-sought';

    protected function configure()
    {
        $this
            ->setDescription('Process licences for Continuation Not Sought (CNS).')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'The date to consider for processing CNS. Defaults to today.',
                (new DateTime())->format('Y-m-d')
            );
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $date = new DateTime($input->getOption('date'));
        $dryRun = $input->getOption('dry-run');

        $result = $this->handleQuery(ContinuationNotSoughtList::create(['date' => $date]));
        $this->logAndWriteVerboseMessage("<info>{$result['count']} Licence(s) found to change to CNS</info>");

        if ($result['count'] === 0) {
            $this->logAndWriteVerboseMessage('<comment>No licences found for CNS processing.</comment>');
            return Command::SUCCESS;
        }

        if (!$dryRun) {
            $result = $this->handleCommand([
                EnqueueContinuationNotSought::create(
                    [
                        'licences' => $result['result'],
                        'date' => $date
                    ]
                )]);

            return $this->outputResult(
                $result,
                'Successfully enqueued CNS processing.',
                'Failed to enqueue CNS processing.'
            );
        } else {
            $this->logAndWriteVerboseMessage('<comment>Dry run mode - no changes have been made.</comment>');
            return Command::SUCCESS;
        }
    }
}
