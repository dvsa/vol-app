<?php

namespace Dvsa\Olcs\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompaniesHouseVsOlcsDiffsExportCommand extends AbstractBatchCommand
{
    protected static $defaultName = 'batch:companies-house-vs-olcs-diffs-export';

    protected function configure()
    {
        $this
            ->setDescription('Find differences between Companies House and OLCS data and export them.')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to save the export file.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        $path = $input->getOption('path');
        if (empty($path)) {
            $this->logAndWriteVerboseMessage('<error>No path specified for the export file.</error>');
            return Command::FAILURE;
        }

        $result = $this->handleCommand([CompaniesHouseVsOlcsDiffsExport::create(['path' => $path])]);

        return $this->outputResult(
            $result,
            "Successfully exported differences to {$path}",
            'Failed to export differences between Companies House and OLCS data.'
        );
    }
}
