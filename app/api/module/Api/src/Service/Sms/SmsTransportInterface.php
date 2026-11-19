<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

interface SmsTransportInterface
{
    /**
     * @param string $to E.164 number
     * @return string|null The Notify notification id, null when the transport has none
     * @throws \Alphagov\Notifications\Exception\NotifyException
     */
    public function send(string $to, string $templateId, string $body, string $reference): ?string;

    /**
     * @return string|null The id of a notification already sent with this reference, null when there is none
     * @throws \Throwable when the lookup itself fails
     */
    public function findByReference(string $reference): ?string;
}
