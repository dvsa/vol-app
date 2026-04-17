<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Command\Batch\ImportUsersFromCsvCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ImportUsersFromCsvCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return ImportUsersFromCsvCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:import-users-from-csv';
    }

    protected function getCommandDTOs(): array
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
