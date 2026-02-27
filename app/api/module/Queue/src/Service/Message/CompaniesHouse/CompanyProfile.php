<?php

namespace Dvsa\Olcs\Queue\Service\Message\CompaniesHouse;

use Dvsa\Olcs\Queue\Service\Message\AbstractMessage;

class CompanyProfile extends AbstractMessage
{
    public const MESSAGE_DELAY = 1;

    #[\Override]
    public function processMessageData(): void
    {
        if (!array_key_exists('companyOrLlpNo', $this->messageData)) {
            throw new \InvalidArgumentException(
                "companyOrLlpNo is required in messageData"
            );
        }
        $this->message['MessageBody'] = $this->messageData['companyOrLlpNo'];
        $this->setDelaySeconds(static::MESSAGE_DELAY);
    }
}
