<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanAccessDocumentsWithIds;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanAccessCorrelatedDocuments;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanCloseConversationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanCreateConversationForOrganisation;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanCreateMessageWithConversation;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanListConversationsByLicence;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationAuthAware;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanAccessConversationMessagesWithConversationId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging\CanListConversationsByOrganisation;

return [
    QueryHandler\Messaging\Conversations\ByLicence::class                            => CanListConversationsByLicence::class,
    QueryHandler\Messaging\Conversations\ByApplicationToLicence::class               => NotIsAnonymousUser::class,
    QueryHandler\Messaging\Conversations\ByCaseToLicence::class                      => IsInternalUser::class,
    QueryHandler\Messaging\ApplicationLicenceList\ByOrganisation::class              => CanAccessOrganisationAuthAware::class,
    QueryHandler\Messaging\ApplicationLicenceList\ByApplicationToOrganisation::class => IsInternalUser::class,
    QueryHandler\Messaging\ApplicationLicenceList\ByCaseToOrganisation::class        => IsInternalUser::class,
    QueryHandler\Messaging\ApplicationLicenceList\ByLicenceToOrganisation::class     => IsInternalUser::class,
    QueryHandler\Messaging\Message\ByConversation::class                             => CanAccessConversationMessagesWithConversationId::class,
    QueryHandler\Messaging\Documents::class                                          => CanAccessCorrelatedDocuments::class,
    CommandHandler\Messaging\Conversation\Close::class                               => CanCloseConversationWithId::class,
    CommandHandler\Messaging\Conversation\Disable::class                             => IsInternalUser::class,
    CommandHandler\Messaging\Conversation\Enable::class                              => IsInternalUser::class,
    CommandHandler\Messaging\EnableFileUpload::class                                 => IsInternalUser::class,
    CommandHandler\Messaging\DisableFileUpload::class                                => IsInternalUser::class,
    CommandHandler\Messaging\Message\Create::class                                   => CanCreateMessageWithConversation::class,
    CommandHandler\Messaging\Conversation\StoreSnapshot::class                       => IsSideEffect::class,
    CommandHandler\Messaging\Conversation\StoreEnhancedSnapshot::class               => IsSideEffect::class,
    QueryHandler\Messaging\Conversations\ByOrganisation::class                       => CanListConversationsByOrganisation::class,
    QueryHandler\Messaging\Subjects\All::class                                       => NotIsAnonymousUser::class,
    CommandHandler\Messaging\Conversation\Create::class                              => CanCreateConversationForOrganisation::class,
    CommandHandler\Email\SendNewMessageNotificationToOperators::class                => IsSideEffect::class,
    QueryHandler\Messaging\Message\UnreadCountByOrganisationAndRoles::class          => CanListConversationsByOrganisation::class,
    QueryHandler\Messaging\Message\UnreadCountByLicenceAndRoles::class               => IsInternalUser::class
];
