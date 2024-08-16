<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\FlagUrgentTasksCommand;
use Dvsa\Olcs\Transfer\Command\Task\FlagUrgentTasks;

class FlagUrgentTasksCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return FlagUrgentTasksCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:flag-urgent-tasks';
    }

    protected function getCommandDTOs()
    {
        return [
            FlagUrgentTasks::create([]),
        ];
    }
}
