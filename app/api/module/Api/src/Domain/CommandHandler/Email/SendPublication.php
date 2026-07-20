<?php

/**
 * Send Publication document via email
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepository;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPublication as SendPublicationEmailCmd;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalLinkCreator;
use Dvsa\Olcs\Email\Data\Message;
use Psr\Container\ContainerInterface;

/**
 * Send Publication document via email
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class SendPublication extends AbstractCommandHandler implements EmailAwareInterface, ToggleAwareInterface
{
    use EmailAwareTrait;
    use ToggleAwareTrait;

    private RetrievalLinkCreator $retrievalLinkCreator;

    protected $repoServiceName = 'Publication';

    protected $template = null;

    public const string TO_EMAIL = 'notifications@vehicle-operator-licensing.service.gov.uk';
    public const string EMAIL_TEMPLATE = 'publication-published';
    public const string EMAIL_SUBJECT = 'email.send-publication';
    public const string EMAIL_POLICE_SUBJECT = 'email.send-publication-police';

    /**
     * Sends an email, with a copy of the publication attached
     *
     * @param CommandInterface|SendPublicationEmailCmd $command command to send publication email
     *
     * @return Result
     */
    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var $repo PublicationRepository $repo
         * @var PublicationEntity $publication
         */
        $repo = $this->getRepo();
        $publication = $repo->fetchUsingId($command);

        $trafficArea = $publication->getTrafficArea();

        //recipients
        $pubType = $publication->getPubType();
        $isPolice = $command->getIsPolice();
        $recipients = $trafficArea->getPublicationRecipients($isPolice, $pubType);

        //get the correct document and email subject line, depending on whether the email is police
        if ($isPolice === 'Y') {
            $document = $publication->getPoliceDocument();
            $subject = self::EMAIL_POLICE_SUBJECT;
        } else {
            $document = $publication->getDocument();
            $subject = self::EMAIL_SUBJECT;
        }

        $templateData = ['filename' => basename((string) $document->getFilename())];

        $message = new Message(self::TO_EMAIL, $subject);
        $message->setBcc($recipients);

        // Public Applications & Decisions publications are delivered via a secure download link
        // (Notify caps attachments at 2MB) when the toggle is on. Police documents keep the legacy
        // attachment path — their OTP-gated, per-recipient link delivery is a later change.
        if ($isPolice !== 'Y' && $this->toggleService->isEnabled(FeatureToggle::RETRIEVE_VIA_LINK)) {
            $retrievalLink = $this->retrievalLinkCreator->create(
                [$document->getId()],
                null,
                'publication',
                'publication:' . $publication->getId(),
            );
            // http://selfserve/ is rewritten to the real selfserve URL by SendEmail::replaceUris.
            $templateData['retrievalLink'] = 'http://selfserve/retrieve/' . $retrievalLink->getToken();
        } else {
            $message->setDocs([$document->getId()]);
        }

        $subjectVars = [
            $pubType,
            $publication->getPublicationNo(),
            $trafficArea->getName()
        ];

        //email subject line
        $message->setSubjectVariables($subjectVars);

        $this->sendEmailTemplate($message, self::EMAIL_TEMPLATE, $templateData);

        $result = new Result();
        $result->addMessage('Publication email sent');

        return $result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->retrievalLinkCreator = $container->get(RetrievalLinkCreator::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
