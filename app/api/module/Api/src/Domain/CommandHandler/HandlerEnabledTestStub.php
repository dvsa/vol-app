<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\HandlerEnabledTrait;

/**
 * Stub used to test the "checkEnabled" functionality
 *
 * @codeCoverageIgnore
 */
class HandlerEnabledTestStub extends AbstractCommandHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;
    use HandlerEnabledTrait;

    protected $toggleConfig = [];

    public function handleCommand(CommandInterface $command)
    {
        return new Result();
    }

    public function setToggleConfig(array $toggleConfig)
    {
        $this->toggleConfig = $toggleConfig;
    }
}
