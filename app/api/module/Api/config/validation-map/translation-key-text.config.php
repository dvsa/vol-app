<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;

return [
    CommandHandler\TranslationKeyText\Update::class => IsSideEffect::class,
    CommandHandler\TranslationKeyText\Create::class => IsSideEffect::class,
    CommandHandler\TranslationKeyText\Delete::class => IsSystemAdmin::class,
];
