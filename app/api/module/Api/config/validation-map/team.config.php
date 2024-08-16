<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanManageUserInternal;

return [
    CommandHandler\Team\CreateTeam::class                                   => CanManageUserInternal::class,
    CommandHandler\Team\UpdateTeam::class                                   => CanManageUserInternal::class,
    CommandHandler\Team\DeleteTeam::class                                   => CanManageUserInternal::class,
    QueryHandler\Team\Team::class                                           => IsInternalUser::class,
    QueryHandler\Team\TeamList::class                                       => IsInternalUser::class,
    QueryHandler\Team\TeamListData::class                                   => IsInternalUser::class,
    QueryHandler\Team\TeamListByTrafficArea::class                          => IsInternalUser::class,
];
