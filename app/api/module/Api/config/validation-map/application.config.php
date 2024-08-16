<?php

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessApplicationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanEditApplicationWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessOrganisationWithOrganisation;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalOrSystemUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalEditOrSystemUser;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalEdit;

return [
    CommandHandler\Application\Schedule41Approve::class => IsInternalEdit::class,
    CommandHandler\Application\Schedule41Refuse::class  => IsInternalEdit::class,
    CommandHandler\Application\Schedule41Cancel::class  => IsInternalOrSystemUser::class,
    CommandHandler\Application\Schedule41Reset::class   => IsInternalEdit::class,
    CommandHandler\Application\NotTakenUpApplication::class => IsInternalEditOrSystemUser::class,
    CommandHandler\Application\CancelApplication::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\CreateApplication::class => CanAccessOrganisationWithOrganisation::class,
    CommandHandler\Application\CreateSnapshot::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\CreateTaxiPhv::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\DeleteTaxiPhv::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\SubmitApplication::class => CanEditApplicationWithId::class,
    CommandHandler\Application\UpdateAddresses::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateApplicationCompletion::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateBusinessDetails::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateDeclaration::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateFinancialEvidence::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateFinancialHistory::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateTaxiPhv::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UpdateTypeOfLicence::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\WithdrawApplication::class => CanEditApplicationWithId::class,
    CommandHandler\Application\UpdatePrivateHireLicence::class => CanAccessApplicationWithId::class,
    CommandHandler\Application\UploadEvidence::class => CanAccessApplicationWithId::class,

    QueryHandler\Application\OutstandingFees::class     => CanAccessApplicationWithId::class,
    QueryHandler\Application\Application::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\Completion::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\Declaration::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\DeclarationUndertakings::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\FinancialEvidence::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\FinancialHistory::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\Review::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\Schedule41Approve::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\Summary::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\TaxiPhv::class => CanAccessApplicationWithId::class,
    QueryHandler\Application\UploadEvidence::class => CanAccessApplicationWithId::class,
];
