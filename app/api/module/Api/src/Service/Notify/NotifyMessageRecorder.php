<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Notify;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Email\Transport\NotifyMessageRecorderInterface;
use Psr\Container\ContainerInterface;

/**
 * Writes a notify_message row for an email Notify has accepted or permanently rejected.
 * The repository is fetched lazily so building the mailer never builds the repositories.
 */
class NotifyMessageRecorder implements NotifyMessageRecorderInterface
{
    /** context key => entity class and setter */
    private const array REFERENCES = [
        'organisation' => [Organisation::class, 'setOrganisation'],
        'licence' => [Licence::class, 'setLicence'],
        'application' => [Application::class, 'setApplication'],
        'document' => [Document::class, 'setDocument'],
        'correspondenceInbox' => [CorrespondenceInbox::class, 'setCorrespondenceInbox'],
        'user' => [User::class, 'setUser'],
    ];

    public function __construct(private readonly ContainerInterface $container)
    {
    }

    #[\Override]
    public function recordEmail(
        string $recipient,
        array $context,
        ?string $notifyId,
        ?string $errorCode,
        ?string $errorMessage,
    ): void {
        $repo = $this->container->get('RepositoryServiceManager')->get('NotifyMessage');
        $now = new \DateTime();
        $accepted = $notifyId !== null;

        $message = new NotifyMessage(
            $repo->getRefdataReference(NotifyMessage::CHANNEL_EMAIL),
            $repo->getRefdataReference($accepted ? NotifyMessage::STATUS_CREATED : NotifyMessage::STATUS_REJECTED),
            mb_substr($recipient, 0, 255),
            $now
        );

        foreach (self::REFERENCES as $key => [$class, $setter]) {
            if (isset($context[$key])) {
                $message->{$setter}($repo->getReference($class, $context[$key]));
            }
        }

        $message->setTemplateName(self::clip($context['templateName'] ?? null, 100));
        $message->setDescription(self::clip($context['description'] ?? null, 255));
        $message->setNotifyTemplateId(self::clip($context['notifyTemplateId'] ?? null, 36));
        $message->setRedirectedTo(self::clip($context['redirectedTo'] ?? null, 255));
        $message->setAttempts(1);
        $message->setStatusUpdatedOn($now);

        if ($accepted) {
            $message->setNotifyId($notifyId);
            $message->setSentOn($now);
        } else {
            $message->setErrorCode(self::clip($errorCode, 32));
            $message->setErrorMessage(self::clip($errorMessage, 1024));
            $message->setCompletedOn($now);
        }

        $repo->save($message);
    }

    private static function clip(mixed $value, int $length): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr($value, 0, $length) : null;
    }
}
