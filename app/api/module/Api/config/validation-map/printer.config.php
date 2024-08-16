<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    CommandHandler\Printer\CreatePrinter::class                                   => IsInternalUser::class,
    CommandHandler\Printer\UpdatePrinter::class                                   => IsInternalUser::class,
    CommandHandler\Printer\DeletePrinter::class                                   => IsInternalUser::class,
    QueryHandler\Printer\Printer::class                                           => IsInternalUser::class,
    QueryHandler\Printer\PrinterList::class                                       => IsInternalUser::class,
];
