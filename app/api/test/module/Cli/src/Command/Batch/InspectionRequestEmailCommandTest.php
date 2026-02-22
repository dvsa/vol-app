<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CompaniesHouseVsOlcsDiffsExportCommand;
use Dvsa\Olcs\Cli\Command\Batch\ImportUsersFromCsvCommand;
use Dvsa\Olcs\Cli\Command\Batch\InspectionRequestEmailCommand;
use Dvsa\Olcs\Cli\Domain\Command\CompaniesHouseVsOlcsDiffsExport;
use Dvsa\Olcs\Cli\Domain\Command\ImportUsersFromCsv;
use Dvsa\Olcs\Email\Domain\Command\ProcessInspectionRequestEmail;
use Laminas\Mvc\Application;
use Symfony\Component\Console\Command\Command;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class InspectionRequestEmailCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return InspectionRequestEmailCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:inspection-request-email';
    }

    protected function getCommandDTOs(): array
    {
        return [
            ProcessInspectionRequestEmail::create([]),
        ];
    }
}
