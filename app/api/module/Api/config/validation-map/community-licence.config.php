<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Cli\Domain\QueryHandler as QueryHandlerCli;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Cli\Domain\CommandHandler as CommandHandlerCli;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalOrSystemUser;

return [
    //  queries
    QueryHandler\CommunityLic\CommunityLicences::class                     => IsInternalUser::class,
    QueryHandler\CommunityLic\CommunityLicence::class                      => IsInternalUser::class,
    QueryHandlerCli\CommunityLic\CommunityLicencesForSuspensionList::class => IsSystemUser::class,
    QueryHandlerCli\CommunityLic\CommunityLicencesForActivationList::class => IsSystemUser::class,

    //  commands
    CommandHandler\CommunityLic\EditSuspension::class                      => IsInternalUser::class,
    CommandHandlerCli\CommunityLic\Activate::class                         => IsSystemUser::class,
    CommandHandlerCli\CommunityLic\Suspend::class                          => IsSystemUser::class,
    CommandHandler\CommunityLic\Application\Create::class                  => IsInternalUser::class,
    CommandHandler\CommunityLic\Application\CreateOfficeCopy::class        => IsInternalUser::class,
    CommandHandler\CommunityLic\Licence\Create::class                      => IsInternalOrSystemUser::class,
    CommandHandler\CommunityLic\Licence\CreateOfficeCopy::class            => IsInternalUser::class,
    CommandHandler\CommunityLic\Reprint::class                             => IsInternalUser::class,
    CommandHandler\CommunityLic\Restore::class                             => IsInternalUser::class,
    CommandHandler\CommunityLic\Stop::class                                => IsInternalUser::class,
    CommandHandler\CommunityLic\Annul::class                               => IsInternalUser::class,
];
