<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Validators\EmailAddress as EmailAddressValidator;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Olcs\Logging\Log\Logger;

/**
 * Create Correspondence Record
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateCorrespondenceRecord extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    protected $repoServiceName = 'CorrespondenceInbox';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Email\CreateCorrespondenceRecord $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Licence $licence */
        $licence = $this->getRepo()->getReference(Licence::class, $command->getLicence());

        $record = new CorrespondenceInbox(
            $licence,
            $this->getRepo()->getReference(Document::class, $command->getDocument())
        );

        $this->getRepo()->save($record);
        $result->addId('correspondenceInbox', $record->getId());
        $result->addMessage('Correspondence record created');

        $validator = new EmailAddressValidator();
        $success = false;

        foreach ($licence->getOrganisation()->getAdminOrganisationUsers() as $orgUser) {
            /** @var OrganisationUserEntity $orgUser */
            $user = $orgUser->getUser();

            $contactDetails = $user->getContactDetails();
            if ($contactDetails === null) {
                Logger::crit(
                    'CreateCorrespondenceRecord: Admin user has no contact_details',
                    [
                        'userId' => $user->getId(),
                        'userLoginId' => $user->getLoginId(),
                        'licenceId' => $licence->getId(),
                        'licenceNo' => $licence->getLicNo(),
                        'organisationId' => $licence->getOrganisation()?->getId(),
                        'organisationName' => $licence->getOrganisation()?->getName(),
                    ]
                );
                continue;
            }

            $emailAddress = $contactDetails->getEmailAddress();
            if (!$validator->isValid($emailAddress)) {
                continue;
            }
            $success = true;
            $message = new \Dvsa\Olcs\Email\Data\Message(
                $emailAddress,
                'email.licensing-information.' . $command->getType()  . '.subject'
            );
            $message->setTranslateToWelsh($user->getTranslateToWelsh());

            $this->sendEmailTemplate(
                $message,
                'licensing-information-' . $command->getType(),
                [
                    'licNo' => $licence->getLicNo(),
                    'operatorName' => $licence->getOrganisation()->getName(),
                    // @NOTE the http://selfserve part gets replaced
                    'url' => 'http://selfserve/correspondence'
                ]
            );
        }

        if (!$success) {
            throw new ValidationException(['internal.granting.email-error']);
        }

        $result->addMessage('Email sent');

        return $result;
    }
}
