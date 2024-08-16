<?php

/**
 * Send Tm User Created Email
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Send Tm User Created Email
 */
final class SendTmUserCreated extends AbstractCommandHandler implements \Dvsa\Olcs\Api\Domain\EmailAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\EmailAwareTrait;

    protected $repoServiceName = 'User';

    protected $extraRepos = ['TransportManagerApplication'];

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
            'email.transport-manager-user-created.subject'
        );

        $message->setTranslateToWelsh($user->getTranslateToWelsh());

        /* @var $tma \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication */
        $tma = $this->getRepo('TransportManagerApplication')->fetchById($command->getTma());

        $this->sendEmailTemplate(
            $message,
            'transport-manager-user-created',
            [
                'organisation' => $tma->getApplication()->getLicence()->getOrganisation()->getName(),
                'reference' => $tma->getApplication()->getLicence()->getLicNo() . '/' . $tma->getApplication()->getId(),
                'loginId' => $user->getLoginId(),
                'url' => sprintf(
                    'http://selfserve/%s/%d/transport-managers/details/%d/edit-details/',
                    ($tma->getApplication()->getIsVariation()) ? 'variation' : 'application',
                    $tma->getApplication()->getId(),
                    $tma->getId()
                )
            ]
        );

        $result = new Result();
        $result->addId('user', $user->getId());
        $result->addMessage('Transport Manager user created email sent');
        return $result;
    }
}
