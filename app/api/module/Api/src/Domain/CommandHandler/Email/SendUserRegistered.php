<?php

/**
 * Send User Registered Email
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Send User Registered Email
 */
final class SendUserRegistered extends AbstractCommandHandler implements \Dvsa\Olcs\Api\Domain\EmailAwareInterface
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
            'email.user-registered.subject'
        );

        $message->setTranslateToWelsh($user->getTranslateToWelsh());

        $this->sendEmailTemplate(
            $message,
            'user-registered',
            [
                'orgName' => $user->getRelatedOrganisationName(),
                'loginId' => $user->getLoginId(),
                // @NOTE the http://selfserve part gets replaced
                'url' => 'http://selfserve/'
            ]
        );

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('User registered email sent');
        return $result;
    }
}
