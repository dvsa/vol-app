<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Licence\CreateSurrenderPsvLicenceTasks;
use Dvsa\Olcs\Api\Domain\Query\Licence\PsvLicenceSurrenderList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePsvLicenceSurrenderTasksCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:create-psv-licence-surrender-tasks';

    protected function configure()
    {
        $this
            ->setDescription('Create tasks to surrender PSV licences that have expired.');
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $date = new DateTime();
        $dryRun = $input->getOption('dry-run');

        $result = $this->handleQuery(PsvLicenceSurrenderList::create(['date' => $date]));
        $this->logAndWriteVerboseMessage("<info>{$result['count']} PSV Licence(s) found to create surrender tasks</info>");

        if ($result['count'] === 0) {
            $this->logAndWriteVerboseMessage('<comment>No PSV licences found for surrender tasks creation.</comment>');
            return Command::SUCCESS;
        }

        if (!$dryRun) {
            $result = $this->handleCommand([
                CreateSurrenderPsvLicenceTasks::create(['ids' => $result['result']])
            ]);

            return $this->outputResult(
                $result,
                'Successfully created surrender tasks for PSV licences.',
                'Failed to create surrender tasks for PSV licences.'
            );
        } else {
            $this->logAndWriteVerboseMessage('<comment>Dry run mode - no changes have been made.</comment>');
            return Command::SUCCESS;
        }
    }
}
