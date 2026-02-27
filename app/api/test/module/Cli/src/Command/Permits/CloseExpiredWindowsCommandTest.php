<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\CloseExpiredWindowsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CloseExpiredWindows;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CloseExpiredWindowsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return CloseExpiredWindowsCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'permits:close-expired-windows';
    }

    protected function getCommandDTOs(): array
    {
        return[CloseExpiredWindows::create(['since' => '-1 day'])];
    }
}
