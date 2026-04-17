<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\CancelUnsubmittedBilateralCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CancelUnsubmittedBilateral;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CancelUnsubmittedBilateralCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return CancelUnsubmittedBilateralCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:permits:cancel-unsubmitted-bilateral';
    }

    protected function getCommandDTOs(): array
    {
        return[CancelUnsubmittedBilateral::create([])];
    }
}
