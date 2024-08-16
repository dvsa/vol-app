<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    CommandHandler\ConditionUndertaking\Create::class     => IsInternalUser::class,
    CommandHandler\ConditionUndertaking\DeleteList::class => IsInternalUser::class,
    CommandHandler\ConditionUndertaking\Update::class     => IsInternalUser::class,

    QueryHandler\ConditionUndertaking\Get::class          => IsInternalUser::class,
    QueryHandler\ConditionUndertaking\GetList::class      => IsInternalUser::class,
];
