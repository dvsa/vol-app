<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Olcs\Logging\Log\Logger;

/**
 * Shared recording step for the Notify transports. Never lets a recording problem touch the send.
 */
trait RecordsNotifyEmails
{
    /**
     * @param array<string, mixed> $payload decoded payload header
     * @return array<string, mixed>
     */
    private function recordingContext(array $payload, ?string $notifyTemplateId): array
    {
        $context = is_array($payload['context'] ?? null) ? $payload['context'] : [];
        if ($notifyTemplateId !== null) {
            $context['notifyTemplateId'] = $notifyTemplateId;
        }

        return $context;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function recordEmail(
        ?NotifyMessageRecorderInterface $recorder,
        string $recipient,
        array $context,
        ?string $notifyId,
        ?string $errorCode,
        ?string $errorMessage,
    ): void {
        if ($recorder === null) {
            return;
        }

        try {
            // With send_all_mail_to the real recipients are in originalTo, the redirect is what we sent to.
            $original = array_values(array_filter((array) ($context['originalTo'] ?? []), 'is_string'));
            unset($context['originalTo']);
            if ($original !== [] && !in_array(strtolower($recipient), array_map('strtolower', $original), true)) {
                $context['redirectedTo'] = $recipient;
                $recipient = implode(', ', $original);
            }

            $recorder->recordEmail($recipient, $context, $notifyId, $errorCode, $errorMessage);
        } catch (\Throwable $e) {
            Logger::err('could not record notify email', ['error' => $e->getMessage()]);
        }
    }
}
