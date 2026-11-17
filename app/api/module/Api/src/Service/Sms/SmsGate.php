<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

class SmsGate
{
    public function __construct(
        private readonly SmsConfig $config,
        private readonly ToggleService $toggleService,
    ) {
    }

    public function isEnabled(): bool
    {
        if (!$this->config->hasNotifyTransport() || $this->config->templateId === null) {
            return false;
        }

        return $this->toggleService->isEnabled(FeatureToggle::SMS_NOTIFICATIONS);
    }
}
