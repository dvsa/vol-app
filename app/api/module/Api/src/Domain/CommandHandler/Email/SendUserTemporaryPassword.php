<?php

/**
 * Send Temporary Password Email
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Send Temporary Password Email
 */
final class SendUserTemporaryPassword extends AbstractCommandHandler implements
    \Dvsa\Olcs\Api\Domain\EmailAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\EmailAwareTrait;

    protected $repoServiceName = 'User';

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $user \Dvsa\Olcs\Api\Entity\User\User */
        $user = $this->getRepo()->fetchById($command->getUser());

        $message = new \Dvsa\Olcs\Email\Data\Message(
            $user->getContactDetails()->getEmailAddress(),
            'email.user-temporary-password.subject'
        );

        $message->setTranslateToWelsh($user->getTranslateToWelsh());

        $this->sendEmailTemplate(
            $message,
            'user-temporary-password',
            [
                'password' => $command->getPassword(),
                // @NOTE the http://selfserve / http://internal part gets replaced
                'url' => $user->isInternal() ? 'http://internal/' : 'http://selfserve/'
            ]
        );

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('User temporary password email sent');
        return $result;
    }
}
