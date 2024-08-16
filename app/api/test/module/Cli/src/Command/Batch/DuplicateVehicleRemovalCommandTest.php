<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleRemoval;
use Dvsa\Olcs\Cli\Command\Batch\DuplicateVehicleRemovalCommand;

class DuplicateVehicleRemovalCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return DuplicateVehicleRemovalCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:duplicate-vehicle-removal';
    }

    protected function getCommandDTOs()
    {
        return [
            ProcessDuplicateVehicleRemoval::create([]),
        ];
    }
}
