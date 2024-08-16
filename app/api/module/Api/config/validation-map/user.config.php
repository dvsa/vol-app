<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsExternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NotIsAnonymousUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\NoValidationRequired;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanAccessUserList;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanManageUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\User\CanReadUser;

return [
    // Queries
    QueryHandler\MyAccount\MyAccount::class                                     => NoValidationRequired::class,
    QueryHandler\User\Partner::class                                            => IsInternalUser::class,
    QueryHandler\User\PartnerList::class                                        => IsInternalUser::class,
    QueryHandler\User\RoleList::class                                           => NoValidationRequired::class,
    QueryHandler\User\UserList::class                                           => CanAccessUserList::class,
    QueryHandler\User\UserListByTrafficArea::class                              => CanAccessUserList::class,
    QueryHandler\User\UserListInternal::class                                   => IsInternalUser::class,
    QueryHandler\User\UserListInternalByTrafficArea::class                      => IsInternalUser::class,
    QueryHandler\User\UserListSelfserve::class                                  => CanManageUser::class,
    QueryHandler\User\UserSelfserve::class                                      => CanReadUser::class,

    // Commands
    CommandHandler\MyAccount\UpdateMyAccountInternal::class                     => IsInternalUser::class,
    CommandHandler\MyAccount\UpdateMyAccountSelfserve::class                    => IsExternalUser::class,
    CommandHandler\User\CreatePartner::class                                    => IsInternalUser::class,
    CommandHandler\User\DeletePartner::class                                    => IsInternalUser::class,
    CommandHandler\User\RegisterUserSelfserve::class                            => NoValidationRequired::class,
    CommandHandler\User\RegisterUserSelfserveFactory::class                     => NoValidationRequired::class,
    CommandHandler\User\RemindUsernameSelfserve::class                          => NoValidationRequired::class,
    CommandHandler\User\UpdatePartner::class                                    => IsInternalUser::class,
    CommandHandler\User\CreateUserSelfserve::class                              => CanManageUser::class,
    CommandHandler\User\CreateUserSelfServeFactory::class                       => CanManageUser::class,
    CommandHandler\User\DeleteUserSelfserve::class                              => CanManageUser::class,
    CommandHandler\User\DeleteUserSelfserveFactory::class                       => CanManageUser::class,
    CommandHandler\User\UpdateUserSelfserve::class                              => CanManageUser::class,
    CommandHandler\User\UpdateUserSelfserveFactory::class                       => CanManageUser::class,
    CommandHandler\User\UpdateUserLastLoginAt::class                            => NotIsAnonymousUser::class,
];
