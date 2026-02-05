<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CleanUpAbandonedVariationsCommand;
use Dvsa\Olcs\Cli\Domain\Command\CleanUpAbandonedVariations;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CleanUpAbandonedVariationsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass(): string
    {
        return CleanUpAbandonedVariationsCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:clean-up-variations';
    }

    protected function getCommandDTOs(): array
    {
        return [
            CleanUpAbandonedVariations::create([]),
        ];
    }
}
