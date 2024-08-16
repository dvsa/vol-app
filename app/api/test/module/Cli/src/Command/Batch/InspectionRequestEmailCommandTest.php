<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Command\Batch\ImportUsersFromCsvCommand;
use Dvsa\Olcs\Cli\Command\Batch\InspectionRequestEmailCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Dvsa\Olcs\Email\Domain\Command\ProcessInspectionRequestEmail;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

class InspectionRequestEmailCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return InspectionRequestEmailCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:inspection-request-email';
    }

    protected function getCommandDTOs()
    {
        return [
            ProcessInspectionRequestEmail::create([]),
        ];
    }
}
