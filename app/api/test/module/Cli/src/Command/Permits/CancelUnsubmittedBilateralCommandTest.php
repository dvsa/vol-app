<?php

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\CancelUnsubmittedBilateralCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\CancelUnsubmittedBilateral;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

class CancelUnsubmittedBilateralCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return CancelUnsubmittedBilateralCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:permits:cancel-unsubmitted-bilateral';
    }

    protected function getCommandDTOs()
    {
        return[CancelUnsubmittedBilateral::create([])];
    }
}
