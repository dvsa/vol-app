<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Alphagov\Notifications\Client;

/**
 * Passthrough template: the text is rendered locally and sent as the ((body)) personalisation.
 */
final class NotifySmsTransport implements SmsTransportInterface
{
    public function __construct(private readonly Client $client)
    {
    }

    #[\Override]
    public function send(string $to, string $templateId, string $body, string $reference): ?string
    {
        $response = $this->client->sendSms($to, $templateId, ['body' => $body], $reference);

        $id = is_array($response) ? ($response['id'] ?? null) : null;

        return is_string($id) ? $id : null;
    }
}
