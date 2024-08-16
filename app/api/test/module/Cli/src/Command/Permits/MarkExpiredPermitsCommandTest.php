<?php

namespace Dvsa\OlcsTest\Cli\Command\Permits;

use Dvsa\Olcs\Cli\Command\Permits\MarkExpiredPermitsCommand;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkExpiredPermits;
use Dvsa\OlcsTest\Cli\Command\Batch\AbstractBatchCommandCases;

class MarkExpiredPermitsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return MarkExpiredPermitsCommand::class;
    }

    protected function getCommandName()
    {
        return 'permits:mark-expired-permits';
    }

    protected function getCommandDTOs()
    {
        return[MarkExpiredPermits::create([])];
    }
}
