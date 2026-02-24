<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\DigitalContinuationRemindersCommand;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\DigitalSendReminders;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class DigitalContinuationRemindersCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return DigitalContinuationRemindersCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:digital-continuation-reminders';
    }

    protected function getCommandDTOs(): array
    {
        return [
            DigitalSendReminders::create([]),
        ];
    }
}
