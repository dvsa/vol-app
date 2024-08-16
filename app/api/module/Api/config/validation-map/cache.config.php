<?php

/**
 * @note the initial handlers added here don't require validation as they need to work for anon users
 * but when adding new handlers be reminded that in most cases they will require validation
 */

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;

return [
    QueryHandler\Cache\ById::class => NoValidationRequired::class,
    QueryHandler\Cache\Replacements::class => NoValidationRequired::class,
    QueryHandler\Cache\TranslationKey::class => NoValidationRequired::class,
    QueryHandler\Cache\Single::class => NoValidationRequired::class,
    QueryHandler\Cache\RecordList::class => NoValidationRequired::class,

    CommandHandler\Cache\ClearForOrganisation::class => IsSideEffect::class,
    CommandHandler\Cache\ClearForLicence::class => IsSideEffect::class,
    CommandHandler\Cache\Generate::class => IsSideEffect::class
];
