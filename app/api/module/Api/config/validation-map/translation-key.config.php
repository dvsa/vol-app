<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    QueryHandler\TranslationKey\ById::class => IsSystemAdmin::class,
    QueryHandler\TranslationKey\GetList::class => IsSystemAdmin::class,
    QueryHandler\Language\GetList::class => IsSystemAdmin::class,

    CommandHandler\TranslationKey\GenerateCache::class => NoValidationRequired::class,
    CommandHandler\TranslationKey\Update::class => IsSystemAdmin::class,
    CommandHandler\TranslationKey\Delete::class => IsSystemAdmin::class,
    CommandHandler\TranslationKey\Create::class => IsSystemAdmin::class
];
