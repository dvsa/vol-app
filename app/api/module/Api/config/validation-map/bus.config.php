<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSideEffect;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanAccessTxcInboxRecordWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr\CanUpdateTxcInboxRecords;

return [
    QueryHandler\Bus\BusNoticePeriodList::class                                         => IsInternalUser::class,
    QueryHandler\Bus\BusServiceTypeList::class                                          => IsInternalUser::class,
    QueryHandler\BusRegSearchView\BusRegSearchViewList::class                           => NoValidationRequired::class,
    QueryHandler\BusRegSearchView\BusRegSearchViewContextList::class                    => NoValidationRequired::class,
    QueryHandler\Bus\BusRegBrowseContextList::class                                     => NoValidationRequired::class,
    QueryHandler\Bus\BusRegBrowseExport::class                                          => NoValidationRequired::class,
    QueryHandler\Bus\BusRegBrowseList::class                                            => NoValidationRequired::class,
    QueryHandler\Bus\SearchViewList::class                                              => IsInternalUser::class,

    // External users
    CommandHandler\Bus\Ebsr\UpdateTxcInbox::class                               => CanUpdateTxcInboxRecords::class,
    QueryHandler\Bus\Ebsr\BusRegWithTxcInbox::class                             => CanAccessTxcInboxRecordWithId::class,
    QueryHandler\Bus\RegistrationHistoryList::class                             => NoValidationRequired::class,
    QueryHandler\Bus\Bus::class                                                 => NoValidationRequired::class,

    CommandHandler\Email\SendBSRNotificationToLTAs::class                      => IsSideEffect::class,
];
