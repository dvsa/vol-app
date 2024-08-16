<?php

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\CloseExpiredWindowsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CloseExpiredWindows;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

class CloseExpiredWindowsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return CloseExpiredWindowsCommand::class;
    }

    protected function getCommandName()
    {
        return 'permits:close-expired-windows';
    }

    protected function getCommandDTOs()
    {
        return[CloseExpiredWindows::create(['since' => '-1 day'])];
    }
}
