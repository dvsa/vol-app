<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\DataDvaNiExport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DataDvaNiExportCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:data-dva-ni-export';

    protected function configure()
    {
        $this
            ->setDescription('Export to csv for Northern Ireland')
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

        $result = $this->handleCommand([DataDvaNiExport::create($params)]);

        return $this->outputResult(
            $result,
            'Successfully exported data to DVA NI.',
            'Failed to export data to DVA NI.'
        );
    }
}
