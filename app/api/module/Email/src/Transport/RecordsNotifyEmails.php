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
            // Only when the handler says send_all_mail_to is active: record the intended
            // recipients and note where it really went.
            $original = array_values(array_filter((array) ($context['originalTo'] ?? []), 'is_string'));
            $redirected = isset($context['redirectTo']);
            unset($context['originalTo'], $context['redirectTo']);
            if ($redirected) {
                $context['redirectedTo'] = $recipient;
                if ($original !== []) {
                    $recipient = implode(', ', $original);
                }
            }

            $recorder->recordEmail($recipient, $context, $notifyId, $errorCode, $errorMessage);
        } catch (\Throwable $e) {
            Logger::err('could not record notify email', ['error' => $e->getMessage()]);
        }
    }
}
