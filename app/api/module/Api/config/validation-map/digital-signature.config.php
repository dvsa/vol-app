<?php

use Dvsa\Olcs\Api\Domain\CommandHandler\DigitalSignature as DigitalSignatureHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;

return [
    DigitalSignatureHandler\UpdateApplication::class => IsSideEffect::class,
    DigitalSignatureHandler\UpdateContinuationDetail::class => IsSideEffect::class,
    DigitalSignatureHandler\UpdateTmApplication::class => IsSideEffect::class,
    DigitalSignatureHandler\UpdateSurrender::class => IsSideEffect::class,
    DigitalSignatureHandler\UpdateSurrenderFactory::class => IsSideEffect::class,
];
