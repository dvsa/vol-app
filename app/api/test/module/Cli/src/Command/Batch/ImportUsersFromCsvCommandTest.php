<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Command\Batch\ImportUsersFromCsvCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

class ImportUsersFromCsvCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return ImportUsersFromCsvCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:import-users-from-csv';
    }

    protected function getCommandDTOs()
    {
        $dtoData = [];
        $dtoData['csvPath'] = $this->additionalArguments['--csv-path'];
        $dtoData['resultCsvPath'] = $this->additionalArguments['--result-csv-path'];
        return [
            ImportUsersFromCsv::create($dtoData),
        ];
    }

    protected $additionalArguments = [
        '--csv-path' => 'test/path',
        '--result-csv-path' => 'test/resultpath'
    ];
}
