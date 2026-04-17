<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsIrfo;
use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsUnlicenced;
use Dvsa\Olcs\Cli\Command\Batch\DatabaseMaintenanceCommand;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DatabaseMaintenanceCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return DatabaseMaintenanceCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:database-maintenance';
    }

    protected function getCommandDTOs(): array
    {
        return [
            FixIsIrfo::create([]),
            FixIsUnlicenced::create([]),
        ];
    }
}
