<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalEdit;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

return [
    QueryHandler\Irfo\IrfoDetails::class                                          => IsInternalUser::class,
    QueryHandler\Irfo\IrfoGvPermit::class                                         => IsInternalUser::class,
    QueryHandler\Irfo\IrfoGvPermitList::class                                     => IsInternalUser::class,
    QueryHandler\Irfo\IrfoGvPermitTypeList::class                                 => IsInternalUser::class,
    QueryHandler\Irfo\IrfoPermitStockList::class                                  => IsInternalUser::class,
    QueryHandler\Irfo\IrfoPsvAuth::class                                          => IsInternalUser::class,
    QueryHandler\Irfo\IrfoPsvAuthContinuationList::class                          => IsInternalUser::class,
    QueryHandler\Irfo\IrfoPsvAuthList::class                                      => IsInternalUser::class,
    QueryHandler\Irfo\IrfoPsvAuthTypeList::class                                  => IsInternalUser::class,
    QueryHandler\Irfo\IrfoCountryList::class                                      => IsInternalUser::class,
    CommandHandler\Irfo\ApproveIrfoGvPermit::class                                => IsInternalUser::class,
    CommandHandler\Irfo\ApproveIrfoPsvAuth::class                                 => IsInternalUser::class,
    CommandHandler\Irfo\CnsIrfoPsvAuth::class                                     => IsInternalUser::class,
    CommandHandler\Irfo\CreateIrfoGvPermit::class                                 => IsInternalUser::class,
    CommandHandler\Irfo\CreateIrfoPermitStock::class                              => IsInternalUser::class,
    CommandHandler\Irfo\CreateIrfoPsvAuth::class                                  => IsInternalUser::class,
    CommandHandler\Irfo\GenerateIrfoGvPermit::class                               => IsInternalEdit::class,
    CommandHandler\Irfo\GenerateIrfoPsvAuth::class                                => IsInternalUser::class,
    CommandHandler\Irfo\GrantIrfoPsvAuth::class                                   => IsInternalUser::class,
    CommandHandler\Irfo\RefuseIrfoGvPermit::class                                 => IsInternalEdit::class,
    CommandHandler\Irfo\RefuseIrfoPsvAuth::class                                  => IsInternalUser::class,
    CommandHandler\Irfo\ResetIrfoGvPermit::class                                  => IsInternalEdit::class,
    CommandHandler\Irfo\UpdateIrfoDetails::class                                  => IsInternalUser::class,
    CommandHandler\Irfo\UpdateIrfoGvPermit::class                                 => IsInternalEdit::class,
    CommandHandler\Irfo\UpdateIrfoPermitStock::class                              => IsInternalUser::class,
    CommandHandler\Irfo\UpdateIrfoPermitStockIssued::class                        => IsInternalUser::class,
    CommandHandler\Irfo\UpdateIrfoPsvAuth::class                                  => IsInternalUser::class,
    CommandHandler\Irfo\WithdrawIrfoGvPermit::class                               => IsInternalEdit::class,
    CommandHandler\Irfo\WithdrawIrfoPsvAuth::class                                => IsInternalUser::class,
    CommandHandler\Irfo\RenewIrfoPsvAuth::class                                   => IsInternalUser::class,
    CommandHandler\Irfo\ResetIrfoPsvAuth::class                                   => IsInternalUser::class,
    CommandHandler\Irfo\PrintIrfoPsvAuthChecklist::class                          => IsInternalUser::class,
];
