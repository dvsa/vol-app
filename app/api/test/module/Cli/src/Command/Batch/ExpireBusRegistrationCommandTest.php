<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\ExpireBusRegistrationCommand;
use Dvsa\Olcs\Cli\Domain\Command\Bus\Expire;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ExpireBusRegistrationCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return ExpireBusRegistrationCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:expire-bus-registration';
    }

    protected function getCommandDTOs(): array
    {
        return [
            Expire::create([]),
        ];
    }
}
