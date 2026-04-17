<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarnings;
use Dvsa\Olcs\Cli\Command\Batch\DuplicateVehicleWarningCommand;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DuplicateVehicleWarningCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return DuplicateVehicleWarningCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:digital-continuation-reminders';
    }

    protected function getCommandDTOs(): array
    {
        return [
            ProcessDuplicateVehicleWarnings::create([]),
        ];
    }
}
