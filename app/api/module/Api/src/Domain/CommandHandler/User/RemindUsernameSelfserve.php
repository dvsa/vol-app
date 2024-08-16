<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUsernameSingle as SendUsernameSingleDto;
use Dvsa\Olcs\Api\Domain\Command\Email\SendUsernameMultiple as SendUsernameMultipleDto;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Remind Username Selfserve
 */
final class RemindUsernameSelfserve extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'User';

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $users = $this->getRepo()->fetchForRemindUsername($command->getLicenceNumber(), $command->getEmailAddress());

        if (empty($users)) {
            $result->addMessage('ERR_USERNAME_NOT_FOUND');
        } elseif (count($users) === 1) {
            // exact match - send email to the user
            $user = array_shift($users);

            $this->handleSideEffect(
                SendUsernameSingleDto::create(
                    [
                        'user' => $user->getId(),
                    ]
                )
            );

            $result->addMessage('USERNAME_REMINDER_SENT_SINGLE');
        } elseif (count($users) > 1) {
            // multiple matches - send email to the organisation's admins
            $this->handleSideEffect(
                SendUsernameMultipleDto::create(
                    [
                        'licenceNumber' => $command->getLicenceNumber(),
                    ]
                )
            );

            $result->addMessage('USERNAME_REMINDER_SENT_MULTIPLE');
        }

        return $result;
    }
}
