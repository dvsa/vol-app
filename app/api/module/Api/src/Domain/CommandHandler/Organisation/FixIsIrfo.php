<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update isIrfo flag where operators are no longer irfo
 */
class FixIsIrfo extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Organisation';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Organisation\FixIsIrfo $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $count = $this->getRepo()->fixIsIrfo();

        return $this->result->addMessage(sprintf('%d organisation(s) changed to isIrfo = 0', $count));
    }
}
