<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToRevokeCurtailSuspend;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToValid;
use Dvsa\Olcs\Cli\Command\Batch\LicenceStatusRulesCommand;

class LicenceStatusRulesCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return LicenceStatusRulesCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:licence-status-rules';
    }

    protected function getCommandDTOs()
    {
        return[
            ProcessToRevokeCurtailSuspend::create([]),
            ProcessToValid::create([])
        ];
    }
}
