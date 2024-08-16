<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Organisation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update isUnlicenced flag where operators no longer have unlicenced licences
 */
class FixIsUnlicenced extends AbstractCommandHandler implements TransactionedInterface
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
        $count = $this->getRepo()->fixIsUnlicenced();

        return $this->result->addMessage(sprintf('%d organisation(s) changed to isUnlicenced = 0', $count));
    }
}
