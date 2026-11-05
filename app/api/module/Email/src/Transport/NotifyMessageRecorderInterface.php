<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

/**
 * Records the final outcome of a Notify email for one recipient (the notify_message table).
 */
interface NotifyMessageRecorderInterface
{
    /**
     * @param array<string, mixed> $context organisation, licence, application, document,
     *     correspondenceInbox, user (ids), templateName, description, notifyTemplateId, redirectedTo
     */
    public function recordEmail(
        string $recipient,
        array $context,
        ?string $notifyId,
        ?string $errorCode,
        ?string $errorMessage,
    ): void;
}
