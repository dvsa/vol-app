<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleWarnings;
use Dvsa\Olcs\Cli\Command\Batch\DuplicateVehicleWarningCommand;

class DuplicateVehicleWarningCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return DuplicateVehicleWarningCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:digital-continuation-reminders';
    }

    protected function getCommandDTOs()
    {
        return [
            ProcessDuplicateVehicleWarnings::create([]),
        ];
    }
}
