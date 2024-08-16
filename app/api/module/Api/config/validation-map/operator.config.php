<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Queries
   QueryHandler\Operator\BusinessDetails::class => Misc\IsInternalUser::class,
   QueryHandler\Operator\UnlicensedBusinessDetails::class => Misc\IsInternalUser::class,

    // Commands
    CommandHandler\Operator\SaveOperator::class => Misc\IsInternalEdit::class,
    CommandHandler\Operator\CreateUnlicensed::class => Misc\IsInternalEdit::class,
    CommandHandler\Operator\UpdateUnlicensed::class => Misc\IsInternalEdit::class,
    CommandHandler\Disqualification\Create::class => Misc\IsInternalEdit::class,
    CommandHandler\Disqualification\Update::class => Misc\IsInternalEdit::class,
];
