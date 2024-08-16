<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataGovUkExportCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:data-gov-uk-export';

    protected function configure()
    {
        $this
            ->setDescription('Export to csv for data.gov.uk')
            ->addOption('report-name', null, InputOption::VALUE_REQUIRED, 'Export report name')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to save export file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $params = [
            'reportName' => $input->getOption('report-name'),
            'path' => $input->getOption('path'),
        ];

        $result = $this->handleCommand([DataGovUkExport::create($params)]);

        return $this->outputResult(
            $result,
            'Successfully exported data to Gov UK.',
            'Failed to export data to Gov UK.'
        );
    }
}
