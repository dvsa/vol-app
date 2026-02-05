<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleRemoval;
use Dvsa\Olcs\Cli\Command\Batch\DuplicateVehicleRemovalCommand;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DuplicateVehicleRemovalCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return DuplicateVehicleRemovalCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:duplicate-vehicle-removal';
    }

    protected function getCommandDTOs(): array
    {
        return [
            ProcessDuplicateVehicleRemoval::create([]),
        ];
    }
}
