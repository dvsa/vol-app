<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

final readonly class SmsConfig
{
    public function __construct(
        public ?string $apiKey,
        public bool $mailpit,
        public ?string $mailpitHost,
        public int $mailpitPort,
        public ?string $templateId,
        public ?string $sendAllSmsTo,
        public bool $isProduction,
    ) {
    }

    /** True when the mail DSN is a govuknotify one (live or mailpit). */
    public function hasNotifyTransport(): bool
    {
        return $this->mailpit || ($this->apiKey !== null && $this->apiKey !== '');
    }
}
