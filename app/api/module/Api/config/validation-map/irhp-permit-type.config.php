<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\IrhpPermitType\ById::class => NotIsAnonymousUser::class,
    QueryHandler\IrhpPermitType\GetList::class => IsInternalUser::class,
];
