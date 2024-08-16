<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Command\Batch\ImportUsersFromCsvCommand;
use Dvsa\Olcs\Cli\Command\Batch\InspectionRequestEmailCommand;
use Dvsa\Olcs\Cli\Command\Batch\InterimEndDateEnforcementCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Dvsa\Olcs\Cli\Domain\Command\InterimEndDateEnforcement;
use Dvsa\Olcs\Email\Domain\Command\ProcessInspectionRequestEmail;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

class InterimEndDateEnforcementCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return InterimEndDateEnforcementCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:interim-end-date-enforcement';
    }

    protected function getCommandDTOs()
    {
        return [
            InterimEndDateEnforcement::create([]),
        ];
    }
}
