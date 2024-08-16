<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\ExpireBusRegistrationCommand;
use Dvsa\Olcs\Cli\Domain\Command\Bus\Expire;

class ExpireBusRegistrationCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return ExpireBusRegistrationCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:expire-bus-registration';
    }

    protected function getCommandDTOs()
    {
        return [
            Expire::create([]),
        ];
    }
}
