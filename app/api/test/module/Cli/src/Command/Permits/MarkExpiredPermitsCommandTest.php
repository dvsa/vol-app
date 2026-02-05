<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\MarkExpiredPermitsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkExpiredPermits;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class MarkExpiredPermitsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return MarkExpiredPermitsCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'permits:mark-expired-permits';
    }

    protected function getCommandDTOs(): array
    {
        return[MarkExpiredPermits::create([])];
    }
}
