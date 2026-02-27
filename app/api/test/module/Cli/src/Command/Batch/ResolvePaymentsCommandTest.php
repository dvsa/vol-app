<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\Transaction\ResolveOutstandingPayments;
use Dvsa\Olcs\Cli\Command\Batch\ResolvePaymentsCommand;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ResolvePaymentsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return ResolvePaymentsCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:remove-read-audit';
    }

    protected function getCommandDTOs(): array
    {
        return[ResolveOutstandingPayments::create([])];
    }
}
