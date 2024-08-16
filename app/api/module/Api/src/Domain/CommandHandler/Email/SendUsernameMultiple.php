<?php

/**
 * Send Username Multiple Email
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Send Username Multiple Email
 */
final class SendUsernameMultiple extends AbstractCommandHandler implements \Dvsa\Olcs\Api\Domain\EmailAwareInterface
{
    use \Dvsa\Olcs\Api\Domain\EmailAwareTrait;

    protected $repoServiceName = 'Licence';

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $licence \Dvsa\Olcs\Api\Entity\Licence\Licence */
        $licence = $this->getRepo()->fetchByLicNo($command->getLicenceNumber());

        $result = new Result();

        foreach ($licence->getOrganisation()->getAdminOrganisationUsers() as $orgUser) {
            // send email to each and every admin of this organisation
            // Note: the email contains ADMIN's username (not a username of the person who forgotten it!)
            $user = $orgUser->getUser();

            $message = new \Dvsa\Olcs\Email\Data\Message(
                $user->getContactDetails()->getEmailAddress(),
                'email.user-forgot-username-multiple.subject'
            );
            $message->setTranslateToWelsh($user->getTranslateToWelsh());

            $this->sendEmailTemplate(
                $message,
                'user-forgot-username-multiple',
                [
                    'loginId' => $user->getLoginId(),
                    // @NOTE the http://selfserve part gets replaced
                    'url' => 'http://selfserve/'
                ]
            );

            $result->addId('user', $user->getId(), true);
        }

        $result->addMessage('Username reminder email sent');
        return $result;
    }
}
