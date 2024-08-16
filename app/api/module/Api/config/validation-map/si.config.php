<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\Si\SiCategoryTypeListData::class                           => IsInternalUser::class,
    QueryHandler\Si\SiPenaltyTypeListData::class                            => IsInternalUser::class,
];
