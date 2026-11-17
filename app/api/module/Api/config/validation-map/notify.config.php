<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    /**
     * Delivery status callback from GOV.UK Notify. Selfserve passes the bearer token on and the API checks it.
     */
    CommandHandler\Notify\UpdateDeliveryStatus::class => Misc\HasValidNotifyCallbackToken::class,
];
