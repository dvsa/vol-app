<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Notify;

use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Email\Transport\NotifyMessageRecorderInterface;
use Psr\Container\ContainerInterface;

/**
 * Writes a notify_message row for an email Notify has accepted or permanently rejected.
 *
 * Emails are sent synchronously inside callers' transactions, so this deliberately bypasses the
 * ORM: a failed flush would close the shared EntityManager and a flush would also write the
 * caller's pending changes. A plain insert (in a savepoint when a transaction is open) can fail
 * without affecting either. The entity manager is fetched lazily.
 */
class NotifyMessageRecorder implements NotifyMessageRecorderInterface
{
    private const string SAVEPOINT = 'notify_message_record';

    /** context key => column */
    private const array REFERENCES = [
        'organisation' => 'organisation_id',
        'licence' => 'licence_id',
        'application' => 'application_id',
        'document' => 'document_id',
        'correspondenceInbox' => 'correspondence_inbox_id',
        'user' => 'user_id',
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
        // Accepted means no error was reported; Notify can accept without returning an id,
        // and the mailpit transport never has one.
        $accepted = $errorCode === null;
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $row = [
            'channel' => NotifyMessage::CHANNEL_EMAIL,
            'status' => $accepted ? NotifyMessage::STATUS_CREATED : NotifyMessage::STATUS_REJECTED,
            'recipient' => mb_substr($recipient, 0, 255),
            'redirected_to' => self::clip($context['redirectedTo'] ?? null, 255),
            'notify_id' => self::clip($notifyId, 36),
            'notify_template_id' => self::clip($context['notifyTemplateId'] ?? null, 36),
            'template_name' => self::clip($context['templateName'] ?? null, 100),
            'description' => self::clip($context['description'] ?? null, 255),
            'error_code' => $accepted ? null : self::clip($errorCode, 32),
            'error_message' => $accepted ? null : self::clip($errorMessage, 1024),
            'attempts' => 1,
            'requested_on' => $now,
            'sent_on' => $accepted ? $now : null,
            'completed_on' => $accepted ? null : $now,
            'status_updated_on' => $now,
            'created_on' => $now,
            'version' => 1,
        ];

        foreach (self::REFERENCES as $key => $column) {
            $row[$column] = isset($context[$key]) ? (int) $context[$key] : null;
        }

        $conn = $this->container->get('doctrine.entitymanager.orm_default')->getConnection();

        if (!$conn->isTransactionActive()) {
            $conn->insert('notify_message', $row);

            return;
        }

        $conn->createSavepoint(self::SAVEPOINT);
        try {
            $conn->insert('notify_message', $row);
            $conn->releaseSavepoint(self::SAVEPOINT);
        } catch (\Throwable $e) {
            $conn->rollbackSavepoint(self::SAVEPOINT);

            throw $e;
        }
    }

    private static function clip(mixed $value, int $length): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr($value, 0, $length) : null;
    }
}
