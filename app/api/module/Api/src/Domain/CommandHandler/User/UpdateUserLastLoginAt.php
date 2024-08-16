<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update User Last Login At
 */
final class UpdateUserLastLoginAt extends AbstractUserCommandHandler
{
    protected $repoServiceName = 'User';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $user = $this->getCurrentUser();
        $user->setLastLoginAt(new \DateTime());

        $this->getRepo()->save($user);

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('User last login at updated successfully');

        return $result;
    }
}
