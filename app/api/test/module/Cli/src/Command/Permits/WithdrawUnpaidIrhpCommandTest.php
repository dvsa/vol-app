<?php

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\WithdrawUnpaidIrhpCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\WithdrawUnpaidIrhp;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

class WithdrawUnpaidIrhpCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return WithdrawUnpaidIrhpCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:permits:withdraw-unpaid';
    }

    protected function getCommandDTOs()
    {
        return[WithdrawUnpaidIrhp::create([])];
    }
}
