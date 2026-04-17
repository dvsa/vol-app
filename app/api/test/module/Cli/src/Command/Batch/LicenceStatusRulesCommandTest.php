<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToRevokeCurtailSuspend;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\ProcessToValid;
use Dvsa\Olcs\Cli\Command\Batch\LicenceStatusRulesCommand;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class LicenceStatusRulesCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return LicenceStatusRulesCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:licence-status-rules';
    }

    protected function getCommandDTOs(): array
    {
        return[
            ProcessToRevokeCurtailSuspend::create([]),
            ProcessToValid::create([])
        ];
    }
}
