<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportUsersFromCsvCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:import-users-from-csv';

    protected function configure()
    {
        $this
            ->setDescription('Import user from csv file')
            ->addOption('csv-path', null, InputOption::VALUE_REQUIRED, 'Path to csv file with users for import')
            ->addOption('result-csv-path', null, InputOption::VALUE_OPTIONAL, 'Path to save result csv file', '<csv-path>-res.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $params = [
            'csvPath' => $input->getOption('csv-path'),
            'resultCsvPath' => $input->getOption('result-csv-path'),
        ];

        $result = $this->handleCommand([ImportUsersFromCsv::create($params)]);

        return $this->outputResult(
            $result,
            'Successfully imported users from CSV.',
            'Failed to import users from CSV.'
        );
    }
}
