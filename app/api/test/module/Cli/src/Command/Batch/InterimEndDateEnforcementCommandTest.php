<?php

declare(strict_types=1);

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

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class InterimEndDateEnforcementCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return InterimEndDateEnforcementCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:interim-end-date-enforcement';
    }

    protected function getCommandDTOs(): array
    {
        return [
            InterimEndDateEnforcement::create([]),
        ];
    }
}
