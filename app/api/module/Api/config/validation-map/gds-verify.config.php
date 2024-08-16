<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanMakeVerifyRequest;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanVerify;

return [
    QueryHandler\GdsVerify\GetAuthRequest::class => CanMakeVerifyRequest::class,
    CommandHandler\GdsVerify\ProcessSignatureResponse::class => CanVerify::class,

];
