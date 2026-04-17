<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\WithdrawUnpaidIrhpCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\WithdrawUnpaidIrhp;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class WithdrawUnpaidIrhpCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return WithdrawUnpaidIrhpCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:permits:withdraw-unpaid';
    }

    protected function getCommandDTOs(): array
    {
        return[WithdrawUnpaidIrhp::create([])];
    }
}
