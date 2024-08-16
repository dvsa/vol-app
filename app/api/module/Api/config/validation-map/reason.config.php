<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\Reason\ReasonList::class               => IsInternalUser::class
];
