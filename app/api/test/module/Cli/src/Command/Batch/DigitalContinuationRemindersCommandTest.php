<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\DigitalContinuationRemindersCommand;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\DigitalSendReminders;

class DigitalContinuationRemindersCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return DigitalContinuationRemindersCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:digital-continuation-reminders';
    }

    protected function getCommandDTOs()
    {
        return [
            DigitalSendReminders::create([]),
        ];
    }
}
