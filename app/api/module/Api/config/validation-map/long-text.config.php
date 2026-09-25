<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    QueryHandler\LongText\ByReferenceKey::class => NoValidationRequired::class,

    // Authoring is a System Admin function.
    QueryHandler\LongText\GetList::class => IsSystemAdmin::class,
    QueryHandler\LongText\ById::class => IsSystemAdmin::class,
    CommandHandler\LongText\Create::class => IsSystemAdmin::class,
    CommandHandler\LongText\Update::class => IsSystemAdmin::class,
];
