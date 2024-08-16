<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolveOutstandingPayments;
use Dvsa\Olcs\Cli\Command\Batch\ResolvePaymentsCommand;

class ResolvePaymentsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return ResolvePaymentsCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:remove-read-audit';
    }

    protected function getCommandDTOs()
    {
        return[ResolveOutstandingPayments::create([])];
    }
}
