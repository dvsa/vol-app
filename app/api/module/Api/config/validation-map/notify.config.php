<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    /**
     * Delivery status callback from GOV.UK Notify. Selfserve checks the bearer token and sends it on as
     * the anonymous user.
     */
    CommandHandler\Notify\UpdateDeliveryStatus::class => Misc\IsAnonymousUser::class,
];
