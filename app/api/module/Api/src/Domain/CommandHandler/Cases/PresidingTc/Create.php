<?php

/**
 * Create Presiding Tc
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as Entity;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Create as CreateCommand;

final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'PresidingTc';
    protected $extraRepos = ['User'];

    /**
     * @param CommandInterface|CreateCommand $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $presidingTc = Entity::create(
            $command->getName(),
            $this->getRepo('User')->fetchById($command->getUser())
        );

        $this->getRepo()->save($presidingTc);

        $this->result->addId('PresidingTc', $presidingTc->getId());
        $this->result->addMessage("PresidingTc '{$presidingTc->getId()}' created");

        return $this->result;
    }
}
