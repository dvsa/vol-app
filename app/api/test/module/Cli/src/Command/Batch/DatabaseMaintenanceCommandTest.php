<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsIrfo;
use Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsUnlicenced;
use Dvsa\Olcs\Cli\Command\Batch\DatabaseMaintenanceCommand;

class DatabaseMaintenanceCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return DatabaseMaintenanceCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:database-maintenance';
    }

    protected function getCommandDTOs()
    {
        return [
            FixIsIrfo::create([]),
            FixIsUnlicenced::create([]),
        ];
    }
}
