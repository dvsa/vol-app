<?php

/**
 * Send User Registered Email
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\ToggleAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Send User Registered Email
 */
final class SendUserRegistered extends AbstractCommandHandler implements \Dvsa\Olcs\Api\Domain\EmailAwareInterface, ToggleAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\EmailAwareTrait;
    use ToggleAwareTrait;

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

        $template = $this->toggleService->isEnabled(
            FeatureToggle::TRANSPORT_CONSULTANT_ROLE)
            ? 'user-registered-tc' : 'user-registered';

        $this->sendEmailTemplate(
            $message,
            $template,
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
