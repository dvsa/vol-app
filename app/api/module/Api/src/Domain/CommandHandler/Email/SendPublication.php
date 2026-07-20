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
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
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

    public const string FLOW_KEY = 'publication';
    public const string POLICE_FLOW_KEY = 'publication-police';

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

        $subjectVars = [
            $pubType,
            $publication->getPublicationNo(),
            $trafficArea->getName(),
        ];
        $filename = basename((string) $document->getFilename());
        $linkEnabled = $this->toggleService->isEnabled(FeatureToggle::RETRIEVE_VIA_LINK);

        if ($linkEnabled && $isPolice === 'Y') {
            // Police copies are sensitive: OTP-gated, one link per recipient so the code lands in
            // that recipient's own mailbox (a single shared link has nowhere to send the OTP).
            $this->sendPoliceRetrievalLinks($publication, $document, $recipients, $subject, $subjectVars, $filename);
        } elseif ($linkEnabled) {
            // Public Applications & Decisions: one shared, unguessable link (Notify caps
            // attachments at 2MB), BCC'd to all recipients.
            $link = $this->retrievalLinkCreator->create(
                [$document->getId()],
                null,
                self::FLOW_KEY,
                'publication:' . $publication->getId(),
            );
            $this->sendPublicationEmail($subject, $subjectVars, $recipients, [
                'filename' => $filename,
                'retrievalLink' => $this->retrievalUrl($link->getToken()),
            ]);
        } else {
            // Legacy: BCC the recipients with the document attached.
            $this->sendPublicationEmail($subject, $subjectVars, $recipients, ['filename' => $filename], [$document->getId()]);
        }

        $result = new Result();
        $result->addMessage('Publication email sent');

        return $result;
    }

    /**
     * Send one email to all recipients (BCC), optionally with the document attached.
     *
     * @param array<string, string> $recipients
     * @param array<string, mixed>   $templateData
     * @param array<int, int>        $docs
     */
    private function sendPublicationEmail(string $subject, array $subjectVars, array $recipients, array $templateData, array $docs = []): void
    {
        $message = new Message(self::TO_EMAIL, $subject);
        $message->setBcc($recipients);
        $message->setSubjectVariables($subjectVars);
        if ($docs !== []) {
            $message->setDocs($docs);
        }

        $this->sendEmailTemplate($message, self::EMAIL_TEMPLATE, $templateData);
    }

    /**
     * Send one OTP-gated retrieval link per police recipient, each bound to that recipient's
     * address so the one-time code is emailed to their own mailbox.
     *
     * @param array<string, string> $recipients
     * @param array<int, mixed>     $subjectVars
     */
    private function sendPoliceRetrievalLinks(
        PublicationEntity $publication,
        DocumentEntity $document,
        array $recipients,
        string $subject,
        array $subjectVars,
        string $filename,
    ): void {
        foreach ($recipients as $email => $name) {
            $link = $this->retrievalLinkCreator->create(
                [$document->getId()],
                (string) $email,
                self::POLICE_FLOW_KEY,
                'publication:' . $publication->getId(),
            );

            $message = new Message(self::TO_EMAIL, $subject);
            $message->setBcc([$email => $name]);
            $message->setSubjectVariables($subjectVars);

            $this->sendEmailTemplate($message, self::EMAIL_TEMPLATE, [
                'filename' => $filename,
                'retrievalLink' => $this->retrievalUrl($link->getToken()),
            ]);
        }
    }

    /**
     * http://selfserve/ is rewritten to the real selfserve URL by SendEmail::replaceUris.
     */
    private function retrievalUrl(string $token): string
    {
        return 'http://selfserve/retrieve/' . $token;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->retrievalLinkCreator = $container->get(RetrievalLinkCreator::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
