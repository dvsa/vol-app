<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Query\Application\NotTakenUpList;
use Dvsa\Olcs\Transfer\Command\Application\NotTakenUpApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessNtuCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:process-ntu';

    protected function configure()
    {
        $this
            ->setDescription('Process Not Taken Up Applications.');
        $this->addCommonOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);
        $dryRun = $input->getOption('dry-run');
        $date = new \DateTime();

        $result = $this->handleQuery(NotTakenUpList::create(['date' => $date->format('Y-m-d')]));

        if (is_array($result) && isset($result['result'])) {
            $this->logAndWriteVerboseMessage(sprintf("<info>%d Application(s) found to change to NTU</info>", count($result['result'])));

            if (!$dryRun) {
                foreach ($result['result'] as $application) {
                    $this->logAndWriteVerboseMessage(sprintf("<comment>Processing Application ID %d</comment>", $application['id']));
                    $this->handleCommand([NotTakenUpApplication::create(['id' => $application['id']])]);
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
