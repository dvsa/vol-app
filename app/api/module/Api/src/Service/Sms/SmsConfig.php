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
        public string $environment = 'local',
    ) {
    }

    /** Non-prod envs share one Notify service, so the reference names the env as well as the row. */
    public function smsReference(int|string $rowId): string
    {
        return $this->environment . '-sms-' . $rowId;
    }

    /** True when the mail DSN is a govuknotify one (live or mailpit). */
    public function hasNotifyTransport(): bool
    {
        return $this->mailpit || ($this->apiKey !== null && $this->apiKey !== '');
    }
}
