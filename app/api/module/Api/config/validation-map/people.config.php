<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application as AppCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence as LicCommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    AppCommandHandler\CreatePeople::class               => Misc\CanAccessApplicationWithId::class,
    AppCommandHandler\DeletePeople::class               => Misc\CanAccessPeopleWithPersonIds::class,
    AppCommandHandler\RestorePeople::class              => Misc\CanAccessPeopleWithPersonIds::class,
    AppCommandHandler\UpdatePeople::class               => Misc\CanAccessPersonWithPerson::class,
    LicCommandHandler\CreatePeople::class               => Misc\CanAccessLicenceWithId::class,
    LicCommandHandler\DeletePeople::class               => Misc\CanAccessPeopleWithPersonIds::class,
    LicCommandHandler\DeletePeopleViaVariation::class   => Misc\CanAccessPeopleWithPersonIds::class,
    LicCommandHandler\UpdatePeople::class               => Misc\CanAccessPersonWithPerson::class,
    QueryHandler\Licence\People::class                  => Misc\CanAccessLicenceWithId::class,
    QueryHandler\Application\People::class              => Misc\CanAccessApplicationWithId::class,
    QueryHandler\Organisation\People::class             => Misc\CanAccessOrganisationWithId::class,
    CommandHandler\OrganisationPerson\Create::class     => Misc\CanAccessOrganisationWithOrganisation::class,
    CommandHandler\OrganisationPerson\DeleteList::class => Misc\CanAccessOrganisationPersonWithIds::class,
    CommandHandler\OrganisationPerson\Update::class     => Misc\CanAccessOrganisationPersonWithId::class,
    QueryHandler\OrganisationPerson\GetSingle::class    => Misc\CanAccessOrganisationPersonWithId::class,
    QueryHandler\Person\Person::class                   => Misc\IsInternalUser::class,


];
