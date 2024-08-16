<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Stub used to test the email aware trait
 *
 * @codeCoverageIgnore
 */
class EmailAwareTraitTestStub extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    public function handleCommand(CommandInterface $command)
    {
        return new Result();
    }
}
