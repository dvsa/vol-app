<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\ContactDetail\CountryList::class => NoValidationRequired::class,
    QueryHandler\ContactDetail\CountrySelectList::class => NoValidationRequired::class,
    QueryHandler\ContactDetail\ContactDetailsList::class => IsInternalUser::class,
    //  phone numbers
    QueryHandler\ContactDetail\PhoneContact\GetList::class => IsInternalUser::class,
    QueryHandler\ContactDetail\PhoneContact\Get::class => IsInternalUser::class,
    CommandHandler\ContactDetails\PhoneContact\Create::class => IsInternalUser::class,
    CommandHandler\ContactDetails\PhoneContact\Update::class => IsInternalUser::class,
    CommandHandler\ContactDetails\PhoneContact\Delete::class => IsInternalUser::class,
];
