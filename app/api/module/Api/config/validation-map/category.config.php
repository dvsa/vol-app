<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\Category\GetList::class               => IsInternalUser::class,
    QueryHandler\SubCategory\GetList::class            => IsInternalUser::class,
    QueryHandler\DocTemplate\GetList::class            => IsInternalUser::class,
    QueryHandler\SubCategoryDescription\GetList::class => IsInternalUser::class,
];
