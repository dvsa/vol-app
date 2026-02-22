<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\FlagUrgentTasksCommand;
use Dvsa\Olcs\Transfer\Command\Task\FlagUrgentTasks;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class FlagUrgentTasksCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return FlagUrgentTasksCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:flag-urgent-tasks';
    }

    protected function getCommandDTOs(): array
    {
        return [
            FlagUrgentTasks::create([]),
        ];
    }
}
