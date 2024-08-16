<?php

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Command\Batch\CleanUpAbandonedVariationsCommand;
use Dvsa\Olcs\Cli\Domain\Command\CleanUpAbandonedVariations;

class CleanUpAbandonedVariationsCommandTest extends AbstractBatchCommandCases
{
    protected function getCommandClass()
    {
        return CleanUpAbandonedVariationsCommand::class;
    }

    protected function getCommandName()
    {
        return 'batch:clean-up-variations';
    }

    protected function getCommandDTOs()
    {
        return [
            CleanUpAbandonedVariations::create([]),
        ];
    }
}
