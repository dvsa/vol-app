<?php

/**
 * Update Presidng TC
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as Entity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\PresidingTc\Update as UpdateCommand;

/**
 * Update a Presiding TC
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'PresidingTc';
    protected $extraRepos = ['User'];

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var UpdateCommand $command
         * @var Entity $presidingTc
         */
        $presidingTc = $this->getRepo()->fetchUsingId($command);

        $presidingTc->update(
            $command->getName(),
            $this->getRepo('User')->fetchById($command->getUser())
        );

        $this->getRepo()->save($presidingTc);

        $this->result->addId('PresidingTc', $presidingTc->getId());
        $this->result->addMessage("PresidingTc '{$presidingTc->getId()}' updated");

        return $this->result;
    }
}
