<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitWindow\IrhpPermitWindowTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class IrhpPermitWindowTraitStub extends AbstractCommandHandler
{
    use IrhpPermitWindowTrait;

    /**
     * @param CommandInterface $command
     *
     * @return Result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleCommand(CommandInterface $command): Result
    {
        return new Result();
    }
}
