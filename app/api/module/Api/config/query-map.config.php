<?php

use Dvsa\Olcs\Api\Domain\Query;
use Dvsa\Olcs\Api\Domain\Query\Bookmark as BookmarkQuery;
use Dvsa\Olcs\Api\Domain\Query\Queue as QueueQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark as BookmarkQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Queue as QueueQueryHandler;
use Dvsa\Olcs\Cli\Domain\Query as QueryCli;
use Dvsa\Olcs\Cli\Domain\QueryHandler as QueryHandlerCli;
use Dvsa\Olcs\Transfer\Query as TransferQuery;

return [
    // Audit
    TransferQuery\Audit\ReadApplication::class => QueryHandler\Audit\ReadApplication::class,
    TransferQuery\Audit\ReadLicence::class => QueryHandler\Audit\ReadLicence::class,
    TransferQuery\Audit\ReadOrganisation::class => QueryHandler\Audit\ReadOrganisation::class,
    TransferQuery\Audit\ReadCase::class => QueryHandler\Audit\ReadCase::class,
    TransferQuery\Audit\ReadTransportManager::class => QueryHandler\Audit\ReadTransportManager::class,
    TransferQuery\Audit\ReadBusReg::class => QueryHandler\Audit\ReadBusReg::class,
    TransferQuery\Audit\ReadIrhpApplication::class => QueryHandler\Audit\ReadIrhpApplication::class,

    // Bookmarks
    BookmarkQuery\LicencePsvDiscCountNotCeased::class => BookmarkQueryHandler\LicencePsvDiscCountNotCeased::class,
    BookmarkQuery\LicenceBundle::class => BookmarkQueryHandler\LicenceBundle::class,
    BookmarkQuery\CaseBundle::class => BookmarkQueryHandler\CaseBundle::class,
    BookmarkQuery\TransportManagerBundle::class => BookmarkQueryHandler\TransportManagerBundle::class,
    BookmarkQuery\DocParagraphBundle::class => BookmarkQueryHandler\DocParagraphBundle::class,
    BookmarkQuery\OppositionBundle::class => BookmarkQueryHandler\OppositionBundle::class,
    BookmarkQuery\StatementBundle::class => BookmarkQueryHandler\StatementBundle::class,
    BookmarkQuery\CommunityLicBundle::class => BookmarkQueryHandler\CommunityLicBundle::class,
    BookmarkQuery\FeeBundle::class => BookmarkQueryHandler\FeeBundle::class,
    BookmarkQuery\ApplicationBundle::class => BookmarkQueryHandler\ApplicationBundle::class,
    BookmarkQuery\ImpoundingBundle::class => BookmarkQueryHandler\ImpoundingBundle::class,
    BookmarkQuery\InterimUnlinkedTm::class => BookmarkQueryHandler\InterimUnlinkedTm::class,
    BookmarkQuery\InterimOperatingCentres::class => BookmarkQueryHandler\InterimOperatingCentres::class,
    BookmarkQuery\IrfoGvPermitBundle::class => BookmarkQueryHandler\IrfoGvPermitBundle::class,
    BookmarkQuery\IrhpApplicationBundle::class => BookmarkQueryHandler\IrhpApplicationBundle::class,
    BookmarkQuery\IrhpPermitBundle::class => BookmarkQueryHandler\IrhpPermitBundle::class,
    BookmarkQuery\IrhpPermitStockBundle::class => BookmarkQueryHandler\IrhpPermitStockBundle::class,
    BookmarkQuery\IrfoPsvAuthBundle::class => BookmarkQueryHandler\IrfoPsvAuthBundle::class,
    BookmarkQuery\OrganisationBundle::class => BookmarkQueryHandler\OrganisationBundle::class,
    BookmarkQuery\UserBundle::class => BookmarkQueryHandler\UserBundle::class,
    BookmarkQuery\BusRegBundle::class => BookmarkQueryHandler\BusRegBundle::class,
    BookmarkQuery\BusFeeTypeBundle::class => BookmarkQueryHandler\BusFeeTypeBundle::class,
    BookmarkQuery\PublicationLinkBundle::class => BookmarkQueryHandler\PublicationLinkBundle::class,
    BookmarkQuery\PublicationBundle::class => BookmarkQueryHandler\PublicationBundle::class,
    BookmarkQuery\PublicationLatestByTaAndTypeBundle::class
        => BookmarkQueryHandler\PublicationLatestByTaAndTypeBundle::class,
    BookmarkQuery\PolicePeopleBundle::class => BookmarkQueryHandler\PolicePeople::class,
    BookmarkQuery\ConditionsUndertakings::class => BookmarkQueryHandler\ConditionsUndertakings::class,
    BookmarkQuery\GoodsDiscBundle::class => BookmarkQueryHandler\GoodsDiscBundle::class,
    BookmarkQuery\PsvDiscBundle::class => BookmarkQueryHandler\PsvDiscBundle::class,
    BookmarkQuery\InterimConditionsUndertakings::class
        => BookmarkQueryHandler\InterimConditionsUndertakings::class,
    BookmarkQuery\FStandingCapitalReserves::class => BookmarkQueryHandler\FStandingCapitalReserves::class,
    BookmarkQuery\PiHearingBundle::class => BookmarkQueryHandler\PiHearingBundle::class,
    BookmarkQuery\PreviousHearingBundle::class => BookmarkQueryHandler\PreviousHearing::class,
    BookmarkQuery\PreviousPublicationByPi::class => BookmarkQueryHandler\PreviousPublication::class,
    BookmarkQuery\PreviousPublicationByApplication::class => BookmarkQueryHandler\PreviousPublication::class,
    BookmarkQuery\PreviousPublicationByLicence::class => BookmarkQueryHandler\PreviousPublication::class,
    BookmarkQuery\TotalContFee::class => BookmarkQueryHandler\TotalContFee::class,
    BookmarkQuery\VehicleBundle::class => BookmarkQueryHandler\VehicleBundle::class,
    BookmarkQuery\VenueBundle::class => BookmarkQueryHandler\VenueBundle::class,
    BookmarkQuery\HearingBundle::class => BookmarkQueryHandler\HearingBundle::class,
    BookmarkQuery\CompaniesHouseCompanyBundle::class => BookmarkQueryHandler\CompaniesHouseCompanyBundle::class,

    // Application
    TransferQuery\Application\Application::class => QueryHandler\Application\Application::class,
    TransferQuery\Application\Completion::class => QueryHandler\Application\Completion::class,
    TransferQuery\Application\Documents::class => QueryHandler\Application\Documents::class,
    TransferQuery\Application\FinancialHistory::class => QueryHandler\Application\FinancialHistory::class,
    TransferQuery\Application\FinancialEvidence::class => QueryHandler\Application\FinancialEvidence::class,
    TransferQuery\Application\PreviousConvictions::class => QueryHandler\Application\PreviousConvictions::class,
    TransferQuery\Application\Safety::class => QueryHandler\Application\Safety::class,
    TransferQuery\Application\Declaration::class => QueryHandler\Application\Declaration::class,
    TransferQuery\Application\DeclarationUndertakings::class => QueryHandler\Application\DeclarationUndertakings::class,
    TransferQuery\Application\LicenceHistory::class => QueryHandler\Application\LicenceHistory::class,
    TransferQuery\Application\TransportManagers::class => QueryHandler\Application\TransportManagers::class,
    TransferQuery\Application\GoodsVehicles::class => QueryHandler\Application\GoodsVehicles::class,
    TransferQuery\Application\GoodsVehiclesExport::class => QueryHandler\Application\GoodsVehiclesExport::class,
    TransferQuery\Application\VehicleDeclaration::class => QueryHandler\Application\VehicleDeclaration::class,
    TransferQuery\Application\Review::class => QueryHandler\Application\Review::class,
    TransferQuery\Application\Overview::class => QueryHandler\Application\Overview::class,
    TransferQuery\Application\EnforcementArea::class => QueryHandler\Application\EnforcementArea::class,
    TransferQuery\Application\Grant::class => QueryHandler\Application\Grant::class,
    TransferQuery\Application\People::class => QueryHandler\Application\People::class,
    TransferQuery\Application\OperatingCentre::class => QueryHandler\Application\OperatingCentre::class,
    TransferQuery\Application\TaxiPhv::class => QueryHandler\Application\TaxiPhv::class,
    TransferQuery\Application\Interim::class => QueryHandler\Application\Interim::class,
    TransferQuery\Application\GetList::class => QueryHandler\Application\GetList::class,
    TransferQuery\Application\OperatingCentres::class => QueryHandler\Application\OperatingCentres::class,
    TransferQuery\Application\PsvVehicles::class => QueryHandler\Application\PsvVehicles::class,
    TransferQuery\Application\Publish::class => QueryHandler\Application\Publish::class,
    TransferQuery\Application\Schedule41Approve::class => QueryHandler\Application\Schedule41Approve::class,
    TransferQuery\Application\Summary::class => QueryHandler\Application\Summary::class,
    TransferQuery\Application\UploadEvidence::class => QueryHandler\Application\UploadEvidence::class,
    Query\Application\NotTakenUpList::class => QueryHandler\Application\NotTakenUpList::class,
    TransferQuery\Application\OutstandingFees::class => QueryHandler\Application\OutstandingFees::class,
    TransferQuery\Fee\InterimRefunds::class => QueryHandler\Fee\InterimRefunds::class,

    // Licence
    TransferQuery\Licence\BusinessDetails::class => QueryHandler\Licence\BusinessDetails::class,
    TransferQuery\Licence\ExistsWithOperatorAdmin::class => QueryHandler\Licence\ExistsWithOperatorAdmin::class,
    TransferQuery\Licence\Licence::class => QueryHandler\Licence\Licence::class,
    TransferQuery\Licence\LicenceWithCorrespondenceCd::class => QueryHandler\Licence\LicenceWithCorrespondenceCd::class,
    TransferQuery\Licence\LicenceByNumber::class => QueryHandler\Licence\LicenceByNumber::class,
    TransferQuery\Licence\LicenceRegisteredAddress::class => QueryHandler\Licence\LicenceRegisteredAddress::class,
    TransferQuery\Licence\TypeOfLicence::class => QueryHandler\Licence\TypeOfLicence::class,
    TransferQuery\Licence\Safety::class => QueryHandler\Licence\Safety::class,
    TransferQuery\Licence\Addresses::class => QueryHandler\Licence\Addresses::class,
    TransferQuery\Licence\TransportManagers::class => QueryHandler\Licence\TransportManagers::class,
    TransferQuery\Licence\PsvDiscs::class => QueryHandler\Licence\PsvDiscs::class,
    TransferQuery\Licence\PsvDiscCount::class => QueryHandler\Licence\PsvDiscCount::class,
    TransferQuery\Licence\GoodsDiscCount::class => QueryHandler\Licence\GoodsDiscCount::class,
    TransferQuery\Licence\GoodsVehicles::class => QueryHandler\Licence\GoodsVehicles::class,
    TransferQuery\Licence\GoodsVehiclesExport::class => QueryHandler\Licence\GoodsVehiclesExport::class,
    TransferQuery\Licence\OtherActiveLicences::class => QueryHandler\Licence\OtherActiveLicences::class,
    TransferQuery\Licence\LicenceDecisions::class => QueryHandler\Licence\LicenceDecisions::class,
    TransferQuery\Licence\Overview::class => QueryHandler\Licence\Overview::class,
    TransferQuery\Licence\EnforcementArea::class => QueryHandler\Licence\EnforcementArea::class,
    TransferQuery\Licence\ConditionUndertaking::class => QueryHandler\Licence\ConditionUndertaking::class,
    TransferQuery\Licence\People::class => QueryHandler\Licence\People::class,
    TransferQuery\Licence\OperatingCentre::class => QueryHandler\Licence\OperatingCentre::class,
    TransferQuery\Licence\TaxiPhv::class => QueryHandler\Licence\TaxiPhv::class,
    TransferQuery\Licence\ContinuationDetail::class => QueryHandler\Licence\ContinuationDetail::class,
    TransferQuery\Licence\GetList::class => QueryHandler\Licence\GetList::class,
    TransferQuery\Licence\OperatingCentres::class => QueryHandler\Licence\OperatingCentres::class,
    TransferQuery\Licence\PsvVehicles::class => QueryHandler\Licence\PsvVehicles::class,
    TransferQuery\Licence\PsvVehiclesExport::class => QueryHandler\Licence\PsvVehiclesExport::class,
    TransferQuery\Licence\Exists::class => QueryHandler\Licence\Exists::class,
    Query\Licence\ContinuationNotSoughtList::class => QueryHandler\Licence\ContinuationNotSoughtList::class,
    Query\Licence\PsvLicenceSurrenderList::class => QueryHandler\Licence\PsvLicenceSurrenderList::class,
    TransferQuery\Licence\Vehicles::class => QueryHandler\Licence\Vehicles::class,

    // LicenceStatusRule
    TransferQuery\LicenceStatusRule\LicenceStatusRule::class => QueryHandler\LicenceStatusRule\LicenceStatusRule::class,

    // Other Licence
    TransferQuery\OtherLicence\OtherLicence::class => QueryHandler\OtherLicence\OtherLicence::class,
    TransferQuery\OtherLicence\GetList::class => QueryHandler\OtherLicence\GetList::class,

    // Organisation
    TransferQuery\Organisation\BusinessDetails::class => QueryHandler\Organisation\BusinessDetails::class,
    TransferQuery\Organisation\Organisation::class => QueryHandler\Organisation\Organisation::class,
    TransferQuery\Organisation\OrganisationAvailableLicences::class => QueryHandler\Organisation\OrganisationAvailableLicences::class,
    TransferQuery\Organisation\OutstandingFees::class => QueryHandler\Organisation\OutstandingFees::class,
    TransferQuery\Organisation\Dashboard::class => QueryHandler\Organisation\Dashboard::class,
    TransferQuery\Organisation\People::class => QueryHandler\Organisation\People::class,
    TransferQuery\Organisation\CpidOrganisation::class
        => QueryHandler\Organisation\CpidOrganisation::class,
    TransferQuery\Organisation\UnlicensedCases::class => QueryHandler\Organisation\UnlicensedCases::class,

    // Variation
    TransferQuery\Variation\Variation::class => QueryHandler\Variation\Variation::class,
    TransferQuery\Variation\TypeOfLicence::class => QueryHandler\Variation\TypeOfLicence::class,
    TransferQuery\Variation\GoodsVehicles::class => QueryHandler\Variation\GoodsVehicles::class,
    TransferQuery\Variation\GoodsVehiclesExport::class => QueryHandler\Variation\GoodsVehiclesExport::class,
    TransferQuery\Variation\PsvVehicles::class => QueryHandler\Variation\PsvVehicles::class,

    // Cases
    TransferQuery\Cases\Cases::class => QueryHandler\Cases\Cases::class,
    TransferQuery\Cases\CasesWithOppositionDates::class => QueryHandler\Cases\CasesWithOppositionDates::class,
    TransferQuery\Cases\CasesWithLicence::class => QueryHandler\Cases\CasesWithLicence::class,
    TransferQuery\Cases\Pi::class => QueryHandler\Cases\Pi::class,
    TransferQuery\Cases\Pi\Hearing::class => QueryHandler\Cases\Pi\Hearing::class,
    TransferQuery\Cases\Pi\HearingList::class => QueryHandler\Cases\Pi\HearingList::class,
    TransferQuery\Cases\Pi\ReportList::class => QueryHandler\Cases\Pi\ReportList::class,
    TransferQuery\Cases\Pi\PiDefinitionList::class => QueryHandler\Cases\Pi\PiDefinitionList::class,
    TransferQuery\Cases\Pi\SlaExceptionList::class => QueryHandler\Cases\Pi\SlaExceptionList::class,
    TransferQuery\Cases\AnnualTestHistory::class => QueryHandler\Cases\AnnualTestHistory::class,
    TransferQuery\Cases\LegacyOffence::class => QueryHandler\Cases\LegacyOffence::class,
    TransferQuery\Cases\LegacyOffenceList::class => QueryHandler\Cases\LegacyOffenceList::class,
    TransferQuery\Cases\Impounding\ImpoundingList::class => QueryHandler\Cases\Impounding\ImpoundingList::class,
    TransferQuery\Cases\Impounding\Impounding::class => QueryHandler\Cases\Impounding\Impounding::class,
    TransferQuery\Cases\ConditionUndertaking\ConditionUndertaking::class =>
        QueryHandler\Cases\ConditionUndertaking\ConditionUndertaking::class,
    TransferQuery\Cases\ConditionUndertaking\ConditionUndertakingList::class =>
        QueryHandler\Cases\ConditionUndertaking\ConditionUndertakingList::class,
    TransferQuery\Cases\ProposeToRevoke\ProposeToRevokeByCase::class
        => QueryHandler\Cases\ProposeToRevoke\ProposeToRevokeByCase::class,

    TransferQuery\Cases\Hearing\Appeal::class => QueryHandler\Cases\Hearing\Appeal::class,
    TransferQuery\Cases\Hearing\AppealByCase::class => QueryHandler\Cases\Hearing\Appeal::class,

    TransferQuery\Cases\Hearing\Stay::class => QueryHandler\Cases\Hearing\Stay::class,
    TransferQuery\Cases\Hearing\StayList::class => QueryHandler\Cases\Hearing\StayList::class,

    TransferQuery\Cases\Statement\Statement::class => QueryHandler\Cases\Statement\Statement::class,
    TransferQuery\Cases\Statement\StatementList::class => QueryHandler\Cases\Statement\StatementList::class,
    TransferQuery\Cases\ByTransportManager::class => QueryHandler\Cases\ByTransportManager::class,
    TransferQuery\Cases\ByLicence::class => QueryHandler\Cases\ByLicence::class,
    TransferQuery\Cases\ByApplication::class => QueryHandler\Cases\ByApplication::class,

    TransferQuery\Cases\Si\Applied\Penalty::class => QueryHandler\Cases\Si\Applied\Penalty::class,
    TransferQuery\Cases\Si\Si::class => QueryHandler\Cases\Si\Si::class,
    TransferQuery\Cases\Si\SiList::class => QueryHandler\Cases\Si\SiList::class,
    TransferQuery\Cases\PresidingTc\GetList::class => QueryHandler\Cases\PresidingTc\GetList::class,
    TransferQuery\Cases\PresidingTc\ById::class => QueryHandler\Cases\PresidingTc\ById::class,

    TransferQuery\Cases\Report\OpenList::class => QueryHandler\Cases\Report\OpenList::class,

    TransferQuery\Venue\VenueList::class => QueryHandler\Venue\VenueList::class,

    // Submission
    TransferQuery\Submission\SubmissionAction::class => QueryHandler\Submission\SubmissionAction::class,
    TransferQuery\Submission\SubmissionSectionComment::class => QueryHandler\Submission\SubmissionSectionComment::class,
    TransferQuery\Submission\Submission::class => QueryHandler\Submission\Submission::class,
    TransferQuery\Submission\SubmissionList::class => QueryHandler\Submission\SubmissionList::class,

    // Processing
    TransferQuery\Processing\History::class => QueryHandler\Processing\History::class,
    TransferQuery\Processing\Note::class => QueryHandler\Processing\Note::class,
    TransferQuery\Processing\NoteList::class => QueryHandler\Processing\NoteList::class,

    // Conviction - NOT Previous Conviction
    TransferQuery\Cases\Conviction\Conviction::class => QueryHandler\Cases\Conviction\Conviction::class,
    TransferQuery\Cases\Conviction\ConvictionList::class => QueryHandler\Cases\Conviction\ConvictionList::class,

    // NonPi
    TransferQuery\Cases\NonPi\Single::class => QueryHandler\Cases\NonPi\Single::class,
    TransferQuery\Cases\NonPi\Listing::class => QueryHandler\Cases\NonPi\Listing::class,

    // Prohibition
    TransferQuery\Cases\Prohibition\Prohibition::class => QueryHandler\Cases\Prohibition\Prohibition::class,
    TransferQuery\Cases\Prohibition\ProhibitionList::class
        => QueryHandler\Cases\Prohibition\ProhibitionList::class,

    // Prohibition / Defect
    TransferQuery\Cases\Prohibition\Defect::class => QueryHandler\Cases\Prohibition\Defect::class,
    TransferQuery\Cases\Prohibition\DefectList::class => QueryHandler\Cases\Prohibition\DefectList::class,

    // Previous Conviction
    TransferQuery\PreviousConviction\PreviousConviction::class
        => QueryHandler\PreviousConviction\PreviousConviction::class,
    TransferQuery\PreviousConviction\GetList::class => QueryHandler\PreviousConviction\GetList::class,

    // Company Subsidiary
    TransferQuery\CompanySubsidiary\CompanySubsidiary::class
        => QueryHandler\CompanySubsidiary\CompanySubsidiary::class,

    // Bus
    TransferQuery\Bus\BusReg::class => QueryHandler\Bus\Bus::class,
    TransferQuery\Bus\BusRegDecision::class => QueryHandler\Bus\BusRegDecision::class,
    TransferQuery\Bus\ShortNoticeByBusReg::class => QueryHandler\Bus\ShortNoticeByBusReg::class,
    TransferQuery\Bus\RegistrationHistoryList::class => QueryHandler\Bus\RegistrationHistoryList::class,
    TransferQuery\Bus\PaginatedRegistrationHistoryList::class =>
        QueryHandler\Bus\PaginatedRegistrationHistoryList::class,
    TransferQuery\Bus\BusNoticePeriodList::class => QueryHandler\Bus\BusNoticePeriodList::class,
    Query\Bus\ByLicenceRoute::class => QueryHandler\Bus\ByLicenceRoute::class,
    TransferQuery\Bus\BusServiceTypeList::class => QueryHandler\Bus\BusServiceTypeList::class,

    // Bus - Ebsr
    TransferQuery\Bus\Ebsr\TxcInboxList::class => QueryHandler\Bus\Ebsr\TxcInboxList::class,
    TransferQuery\Bus\Ebsr\BusRegWithTxcInbox::class => QueryHandler\Bus\Ebsr\BusRegWithTxcInbox::class,
    TransferQuery\Bus\Ebsr\EbsrSubmissionList::class => QueryHandler\Bus\Ebsr\EbsrSubmissionList::class,
    TransferQuery\Bus\Ebsr\OrganisationUnprocessedList::class =>
        QueryHandler\Bus\Ebsr\OrganisationUnprocessedList::class,
    TransferQuery\Bus\Ebsr\EbsrSubmission::class => QueryHandler\Bus\Ebsr\EbsrSubmission::class,

    // Trailer
    TransferQuery\Licence\Trailers::class => QueryHandler\Licence\Trailers::class,
    TransferQuery\Trailer\Trailer::class => QueryHandler\Trailer\Trailer::class,

    // Grace Periods
    TransferQuery\GracePeriod\GracePeriod::class => QueryHandler\GracePeriod\GracePeriod::class,
    TransferQuery\GracePeriod\GracePeriods::class => QueryHandler\GracePeriod\GracePeriods::class,

    // Irfo
    TransferQuery\Irfo\IrfoDetails::class => QueryHandler\Irfo\IrfoDetails::class,
    TransferQuery\Irfo\IrfoGvPermit::class => QueryHandler\Irfo\IrfoGvPermit::class,
    TransferQuery\Irfo\IrfoGvPermitList::class => QueryHandler\Irfo\IrfoGvPermitList::class,
    TransferQuery\Irfo\IrfoGvPermitTypeList::class => QueryHandler\Irfo\IrfoGvPermitTypeList::class,
    TransferQuery\Irfo\IrfoPermitStockList::class => QueryHandler\Irfo\IrfoPermitStockList::class,
    TransferQuery\Irfo\IrfoPsvAuth::class => QueryHandler\Irfo\IrfoPsvAuth::class,
    TransferQuery\Irfo\IrfoPsvAuthContinuationList::class => QueryHandler\Irfo\IrfoPsvAuthContinuationList::class,
    TransferQuery\Irfo\IrfoPsvAuthList::class => QueryHandler\Irfo\IrfoPsvAuthList::class,
    TransferQuery\Irfo\IrfoPsvAuthTypeList::class => QueryHandler\Irfo\IrfoPsvAuthTypeList::class,
    TransferQuery\Irfo\IrfoCountryList::class => QueryHandler\Irfo\IrfoCountryList::class,

    // Publication
    TransferQuery\Publication\Recipient::class => QueryHandler\Publication\Recipient::class,
    TransferQuery\Publication\RecipientList::class => QueryHandler\Publication\RecipientList::class,
    TransferQuery\Publication\PublicationLinkTmList::class => QueryHandler\Publication\PublicationLinkByTm::class,
    TransferQuery\Publication\PublicationLinkList::class
        => QueryHandler\Publication\PublicationLinkList::class,
    TransferQuery\Publication\PublicationLink::class => QueryHandler\Publication\PublicationLink::class,
    TransferQuery\Publication\PendingList::class => QueryHandler\Publication\PendingList::class,
    TransferQuery\Publication\PublishedList::class => QueryHandler\Publication\PublishedList::class,

    // My Account
    TransferQuery\MyAccount\MyAccount::class => QueryHandler\MyAccount\MyAccount::class,

    // User
    TransferQuery\User\Partner::class => QueryHandler\User\Partner::class,
    TransferQuery\User\PartnerList::class => QueryHandler\User\PartnerList::class,
    TransferQuery\User\User::class => QueryHandler\User\User::class,
    TransferQuery\User\UserList::class => QueryHandler\User\UserList::class,
    Query\User\UserListByTrafficArea::class => QueryHandler\User\UserListByTrafficArea::class,
    TransferQuery\User\UserSelfserve::class => QueryHandler\User\UserSelfserve::class,
    TransferQuery\User\UserListSelfserve::class => QueryHandler\User\UserListSelfserve::class,
    TransferQuery\User\RoleList::class => QueryHandler\User\RoleList::class,
    TransferQuery\User\UserListInternal::class => QueryHandler\User\UserListInternal::class,
    Query\User\UserListInternalByTrafficArea::class => QueryHandler\User\UserListInternalByTrafficArea::class,
    TransferQuery\User\OperatorAdminForOrganisationHasLoggedIn::class => QueryHandler\User\OperatorAdminForOrganisationHasLoggedIn::class,

    // User
    TransferQuery\Team\Team::class => QueryHandler\Team\Team::class,
    TransferQuery\Team\TeamList::class => QueryHandler\Team\TeamList::class,
    TransferQuery\Team\TeamListData::class => QueryHandler\Team\TeamListData::class,
    Query\Team\TeamListByTrafficArea::class => QueryHandler\Team\TeamListByTrafficArea::class,

    // TeamPrinter
    TransferQuery\TeamPrinter\TeamPrinterExceptionsList::class =>
        QueryHandler\TeamPrinter\TeamPrinterExceptionsList::class,
    TransferQuery\TeamPrinter\TeamPrinter::class => QueryHandler\TeamPrinter\TeamPrinter::class,

    // Printer
    TransferQuery\Printer\Printer::class => QueryHandler\Printer\Printer::class,
    TransferQuery\Printer\PrinterList::class => QueryHandler\Printer\PrinterList::class,

    // Workshop
    TransferQuery\Workshop\Workshop::class => QueryHandler\Workshop\Workshop::class,

    // Correspondence
    TransferQuery\Correspondence\Correspondence::class => QueryHandler\Correspondence\Correspondence::class,
    TransferQuery\Correspondence\Correspondences::class => QueryHandler\Correspondence\Correspondences::class,

    // Transaction (formerly 'Payment')
    TransferQuery\Transaction\Transaction::class => QueryHandler\Transaction\Transaction::class,
    TransferQuery\Transaction\TransactionByReference::class => QueryHandler\Transaction\TransactionByReference::class,

    // CommunityLic
    TransferQuery\CommunityLic\CommunityLicences::class => QueryHandler\CommunityLic\CommunityLicences::class,
    TransferQuery\CommunityLic\CommunityLicence::class  => QueryHandler\CommunityLic\CommunityLicence::class,
    QueryCli\CommunityLic\CommunityLicencesForSuspensionList::class =>
        QueryHandlerCli\CommunityLic\CommunityLicencesForSuspensionList::class,
    QueryCli\CommunityLic\CommunityLicencesForActivationList::class =>
        QueryHandlerCli\CommunityLic\CommunityLicencesForActivationList::class,

    // Document
    TransferQuery\Document\TemplateParagraphs::class => QueryHandler\Document\TemplateParagraphs::class,
    TransferQuery\Document\Document::class => QueryHandler\Document\Document::class,
    TransferQuery\Document\Letter::class => QueryHandler\Document\Letter::class,
    TransferQuery\Document\DocumentList::class => QueryHandler\Document\DocumentList::class,
    TransferQuery\Document\Download::class => QueryHandler\Document\Download::class,
    TransferQuery\Document\DownloadGuide::class => QueryHandler\Document\DownloadGuide::class,
    TransferQuery\Document\PrintLetter::class => QueryHandler\Document\PrintLetter::class,

    // Transport Manager Application
    TransferQuery\TransportManagerApplication\GetDetails::class
        => QueryHandler\TransportManagerApplication\GetDetails::class,
    TransferQuery\TransportManagerApplication\GetList::class
        => QueryHandler\TransportManagerApplication\GetList::class,
    TransferQuery\TransportManagerApplication\GetForResponsibilities::class
        => QueryHandler\TransportManagerApplication\GetForResponsibilities::class,
    TransferQuery\TransportManagerApplication\Review::class => QueryHandler\TransportManagerApplication\Review::class,

    // Transport Manager Licence
    TransferQuery\TransportManagerLicence\GetForResponsibilities::class
        => QueryHandler\TransportManagerLicence\GetForResponsibilities::class,
    TransferQuery\TransportManagerLicence\GetList::class
        => QueryHandler\TransportManagerLicence\GetList::class,
    TransferQuery\TransportManagerLicence\GetListByVariation::class
        => QueryHandler\TransportManagerLicence\GetListByVariation::class,

    // TmEmployment
    TransferQuery\TmEmployment\GetSingle::class => QueryHandler\TmEmployment\GetSingle::class,
    TransferQuery\TmEmployment\GetList::class => QueryHandler\TmEmployment\GetList::class,

    // Bus Reg History View
    TransferQuery\Bus\HistoryList::class => QueryHandler\Bus\HistoryList::class,

    // Bus Reg Search View
    TransferQuery\Bus\SearchViewList::class => QueryHandler\Bus\SearchViewList::class,

    // Bus Reg Filtered List
    TransferQuery\BusRegSearchView\BusRegSearchViewList::class =>
        QueryHandler\BusRegSearchView\BusRegSearchViewList::class,
    TransferQuery\BusRegSearchView\BusRegSearchViewContextList::class =>
        QueryHandler\BusRegSearchView\BusRegSearchViewContextList::class,

    TransferQuery\Bus\BusRegBrowseContextList::class =>
        QueryHandler\Bus\BusRegBrowseContextList::class,
    TransferQuery\Bus\BusRegBrowseExport::class =>
        QueryHandler\Bus\BusRegBrowseExport::class,
    TransferQuery\Bus\BusRegBrowseList::class => QueryHandler\Bus\BusRegBrowseList::class,

    // Fee
    TransferQuery\Fee\Fee::class => QueryHandler\Fee\Fee::class,
    TransferQuery\Fee\FeeList::class => QueryHandler\Fee\FeeList::class,

    // Fee Type
    TransferQuery\Fee\FeeType::class => QueryHandler\Fee\FeeType::class,
    TransferQuery\Fee\FeeTypeList::class => QueryHandler\Fee\FeeTypeList::class,
    TransferQuery\Fee\GetLatestFeeType::class => QueryHandler\Fee\GetLatestFeeType::class,
    TransferQuery\FeeType\GetList::class => QueryHandler\FeeType\GetList::class,
    TransferQuery\FeeType\GetDistinctList::class => QueryHandler\FeeType\GetDistinctList::class,

    // Operator
    TransferQuery\Operator\BusinessDetails::class => QueryHandler\Operator\BusinessDetails::class,
    TransferQuery\Operator\UnlicensedBusinessDetails::class => QueryHandler\Operator\UnlicensedBusinessDetails::class,
    TransferQuery\Operator\UnlicensedVehicles::class => QueryHandler\Operator\UnlicensedVehicles::class,

    // Licence Vehicle
    TransferQuery\LicenceVehicle\LicenceVehicle::class => QueryHandler\LicenceVehicle\LicenceVehicle::class,
    TransferQuery\LicenceVehicle\PsvLicenceVehicle::class => QueryHandler\LicenceVehicle\PsvLicenceVehicle::class,
    TransferQuery\LicenceVehicle\LicenceVehiclesById::class => QueryHandler\LicenceVehicle\LicenceVehiclesById::class,

    // Opposition
    TransferQuery\Opposition\Opposition::class => QueryHandler\Opposition\Opposition::class,
    TransferQuery\Opposition\OppositionList::class => QueryHandler\Opposition\OppositionList::class,

    // Complaint
    TransferQuery\Complaint\Complaint::class => QueryHandler\Complaint\Complaint::class,
    TransferQuery\Complaint\ComplaintList::class => QueryHandler\Complaint\ComplaintList::class,
    TransferQuery\EnvironmentalComplaint\EnvironmentalComplaint::class =>
        QueryHandler\EnvironmentalComplaint\EnvironmentalComplaint::class,
    TransferQuery\EnvironmentalComplaint\EnvironmentalComplaintList::class =>
        QueryHandler\EnvironmentalComplaint\EnvironmentalComplaintList::class,

    // Inspection Request
    TransferQuery\InspectionRequest\OperatingCentres::class => QueryHandler\InspectionRequest\OperatingCentres::class,
    TransferQuery\InspectionRequest\ApplicationInspectionRequestList::class =>
        QueryHandler\InspectionRequest\ApplicationInspectionRequestList::class,
    TransferQuery\InspectionRequest\LicenceInspectionRequestList::class =>
        QueryHandler\InspectionRequest\LicenceInspectionRequestList::class,
    TransferQuery\InspectionRequest\InspectionRequest::class => QueryHandler\InspectionRequest\InspectionRequest::class,

    // Change of Entity
    TransferQuery\ChangeOfEntity\ChangeOfEntity::class => QueryHandler\ChangeOfEntity\ChangeOfEntity::class,

    // ConditionUndertaking
    TransferQuery\ConditionUndertaking\GetList::class => QueryHandler\ConditionUndertaking\GetList::class,
    TransferQuery\ConditionUndertaking\Get::class => QueryHandler\ConditionUndertaking\Get::class,

    // Task
    TransferQuery\Task\TaskList::class => QueryHandler\Task\TaskList::class,
    TransferQuery\Task\Task::class => QueryHandler\Task\Task::class,
    TransferQuery\Task\TaskDetails::class => QueryHandler\Task\TaskDetails::class,

    // Tm Responsibilities
    TransferQuery\TmResponsibilities\TmResponsibilitiesList::class =>
        QueryHandler\TmResponsibilities\TmResponsibilitiesList::class,
    TransferQuery\TmResponsibilities\GetDocumentsForResponsibilities::class =>
        QueryHandler\TmResponsibilities\GetDocumentsForResponsibilities::class,

    // Companies House
    QueryCli\CompaniesHouse\Organisations::class => QueryHandlerCli\CompaniesHouse\Organisations::class,
    TransferQuery\CompaniesHouse\AlertList::class => QueryHandler\CompaniesHouse\AlertList::class,
    TransferQuery\CompaniesHouse\ByNumber::class => QueryHandler\CompaniesHouse\ByNumber::class,

    // Queue
    QueueQuery\NextItem::class => QueueQueryHandler\NextItem::class,

    // TmCaseDecision
    TransferQuery\TmCaseDecision\GetByCase::class =>
        QueryHandler\TmCaseDecision\GetByCase::class,

    // TmQualification
    TransferQuery\TmQualification\TmQualificationsList::class =>
        QueryHandler\TmQualification\TmQualificationsList::class,
    TransferQuery\TmQualification\TmQualification::class => QueryHandler\TmQualification\TmQualification::class,

    // Transport Manager
    TransferQuery\Tm\TransportManager::class => QueryHandler\Tm\TransportManager::class,
    TransferQuery\Tm\Documents::class => QueryHandler\Tm\Documents::class,

    // Search
    TransferQuery\Search\Licence::class => QueryHandler\Search\Licence::class,

    // Application Operating Centres
    TransferQuery\ApplicationOperatingCentre\ApplicationOperatingCentre::class
        => QueryHandler\ApplicationOperatingCentre\ApplicationOperatingCentre::class,

    // Licence Operating Centres
    TransferQuery\LicenceOperatingCentre\LicenceOperatingCentre::class
        => QueryHandler\LicenceOperatingCentre\LicenceOperatingCentre::class,

    // Variation Operating Centres
    TransferQuery\VariationOperatingCentre\VariationOperatingCentre::class
        => QueryHandler\VariationOperatingCentre\VariationOperatingCentre::class,

    // Organisation Person
    TransferQuery\OrganisationPerson\GetSingle::class => QueryHandler\OrganisationPerson\GetSingle::class,

    // Disc Printing
    TransferQuery\DiscSequence\DiscPrefixes::class => QueryHandler\DiscSequence\DiscPrefixes::class,
    TransferQuery\DiscSequence\DiscsNumbering::class => QueryHandler\DiscSequence\DiscsNumbering::class,

    // Person
    TransferQuery\Person\Person::class => QueryHandler\Person\Person::class,

    // Continuation Detail
    TransferQuery\ContinuationDetail\ChecklistReminders::class =>
        QueryHandler\ContinuationDetail\ChecklistReminders::class,
    TransferQuery\ContinuationDetail\GetList::class => QueryHandler\ContinuationDetail\GetList::class,
    TransferQuery\ContinuationDetail\LicenceChecklist::class => QueryHandler\ContinuationDetail\LicenceChecklist::class,
    TransferQuery\ContinuationDetail\Review::class => QueryHandler\ContinuationDetail\Review::class,
    TransferQuery\ContinuationDetail\Get::class => QueryHandler\ContinuationDetail\Get::class,

    // System
    TransferQuery\System\FinancialStandingRate::class => QueryHandler\System\FinancialStandingRate::class,
    TransferQuery\System\FinancialStandingRateList::class => QueryHandler\System\FinancialStandingRateList::class,

    // Cpms
    TransferQuery\Cpms\ReportList::class => QueryHandler\Cpms\ReportList::class,
    TransferQuery\Cpms\ReportStatus::class => QueryHandler\Cpms\ReportStatus::class,

    // Address
    TransferQuery\Address\GetAddress::class => QueryHandler\Address\GetAddressFactory::class,
    TransferQuery\Address\GetList::class => QueryHandler\Address\GetListFactory::class,

    TransferQuery\Category\GetList::class => QueryHandler\Category\GetList::class,
    TransferQuery\SubCategory\GetList::class => QueryHandler\SubCategory\GetList::class,
    TransferQuery\SubCategoryDescription\GetList::class => QueryHandler\SubCategoryDescription\GetList::class,
    TransferQuery\DocTemplate\GetList::class => QueryHandler\DocTemplate\GetList::class,
    TransferQuery\DocTemplate\FullList::class => QueryHandler\DocTemplate\FullList::class,
    TransferQuery\DocTemplate\ById::class => QueryHandler\DocTemplate\ById::class,

    TransferQuery\ContactDetail\CountryList::class => QueryHandler\ContactDetail\CountryList::class,
    TransferQuery\ContactDetail\CountrySelectList::class => QueryHandler\ContactDetail\CountrySelectList::class,
    TransferQuery\ContactDetail\ContactDetailsList::class => QueryHandler\ContactDetail\ContactDetailsList::class,
    TransferQuery\ContactDetail\PhoneContact\Get::class => QueryHandler\ContactDetail\PhoneContact\Get::class,
    TransferQuery\ContactDetail\PhoneContact\GetList::class => QueryHandler\ContactDetail\PhoneContact\GetList::class,
    TransferQuery\TrafficArea\TrafficAreaInternalList::class => QueryHandler\TrafficArea\TrafficAreaInternalList::class,
    TransferQuery\TrafficArea\TrafficAreaList::class => QueryHandler\TrafficArea\TrafficAreaList::class,
    TransferQuery\TrafficArea\Get::class => QueryHandler\TrafficArea\Get::class,

    TransferQuery\RefData\RefDataList::class => QueryHandler\RefData\RefDataList::class,
    TransferQuery\LocalAuthority\LocalAuthorityList::class => QueryHandler\LocalAuthority\LocalAuthorityList::class,
    TransferQuery\LocalAuthority\ById::class => QueryHandler\LocalAuthority\ById::class,

    // SystemParameter
    TransferQuery\SystemParameter\SystemParameter::class => QueryHandler\SystemParameter\SystemParameter::class,
    TransferQuery\SystemParameter\SystemParameterList::class => QueryHandler\SystemParameter\SystemParameterList::class,

    // FeatureToggle
    TransferQuery\FeatureToggle\ById::class => QueryHandler\FeatureToggle\ById::class,
    TransferQuery\FeatureToggle\GetList::class => QueryHandler\FeatureToggle\GetList::class,
    TransferQuery\FeatureToggle\IsEnabled::class => QueryHandler\FeatureToggle\IsEnabled::class,

    // IRHP Permit Stock
    TransferQuery\IrhpPermitStock\ById::class => QueryHandler\IrhpPermitStock\ById::class,
    TransferQuery\IrhpPermitStock\GetList::class => QueryHandler\IrhpPermitStock\GetList::class,
    TransferQuery\IrhpPermitStock\AvailableBilateral::class => QueryHandler\IrhpPermitStock\AvailableBilateral::class,

    // IRHP Permit Type
    TransferQuery\IrhpPermitType\ById::class => QueryHandler\IrhpPermitType\ById::class,
    TransferQuery\IrhpPermitType\GetList::class => QueryHandler\IrhpPermitType\GetList::class,

    // IRHP Permit Window
    TransferQuery\IrhpPermitWindow\ById::class => QueryHandler\IrhpPermitWindow\ById::class,
    TransferQuery\IrhpPermitWindow\GetList::class => QueryHandler\IrhpPermitWindow\GetList::class,
    TransferQuery\IrhpPermitWindow\OpenByCountry::class => QueryHandler\IrhpPermitWindow\OpenByCountry::class,
    TransferQuery\IrhpPermitWindow\OpenByType::class => QueryHandler\IrhpPermitWindow\OpenByType::class,

    // IRHP Permit Range
    TransferQuery\IrhpPermitRange\ById::class => QueryHandler\IrhpPermitRange\ById::class,
    TransferQuery\IrhpPermitRange\GetList::class => QueryHandler\IrhpPermitRange\GetList::class,

    // IRHP Permit Sector
    TransferQuery\IrhpPermitSector\GetList::class => QueryHandler\IrhpPermitSector\GetList::class,

    // IRHP Permit Jurisdiction
    TransferQuery\IrhpPermitJurisdiction\GetList::class => QueryHandler\IrhpPermitJurisdiction\GetList::class,

    // Permit Printing
    TransferQuery\Permits\ReadyToPrintType::class => QueryHandler\Permits\ReadyToPrintType::class,
    TransferQuery\Permits\ReadyToPrintCountry::class => QueryHandler\Permits\ReadyToPrintCountry::class,
    TransferQuery\Permits\ReadyToPrintStock::class => QueryHandler\Permits\ReadyToPrintStock::class,
    TransferQuery\Permits\ReadyToPrintRangeType::class => QueryHandler\Permits\ReadyToPrintRangeType::class,
    TransferQuery\Permits\ReadyToPrint::class => QueryHandler\Permits\ReadyToPrint::class,
    TransferQuery\Permits\ReadyToPrintConfirm::class => QueryHandler\Permits\ReadyToPrintConfirm::class,

    // IRHP Permits
    TransferQuery\IrhpPermit\GetListByIrhpId::class => QueryHandler\IrhpPermit\GetListByIrhpId::class,
    TransferQuery\IrhpPermit\ById::class => QueryHandler\IrhpPermit\ById::class,
    TransferQuery\IrhpPermit\GetListByLicence::class => QueryHandler\IrhpPermit\GetListByLicence::class,
    TransferQuery\IrhpPermit\UniqueCountriesByLicence::class => QueryHandler\IrhpPermit\UniqueCountriesByLicence::class,

    // IRHP Candidate Permits
    TransferQuery\IrhpCandidatePermit\ById::class => QueryHandler\IrhpCandidatePermit\ById::class,
    TransferQuery\IrhpCandidatePermit\GetListByIrhpApplication::class
        => QueryHandler\IrhpCandidatePermit\GetListByIrhpApplication::class,
    TransferQuery\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged::class
        => QueryHandler\IrhpCandidatePermit\GetListByIrhpApplication::class,

    // Admin :: Data Retention
    TransferQuery\DataRetention\GetRule::class => QueryHandler\DataRetention\GetRule::class,
    TransferQuery\DataRetention\RuleList::class => QueryHandler\DataRetention\RuleList::class,
    TransferQuery\DataRetention\RuleAdmin::class => QueryHandler\DataRetention\RuleAdmin::class,
    TransferQuery\DataRetention\Records::class => QueryHandler\DataRetention\Records::class,
    TransferQuery\DataRetention\GetProcessedList::class => QueryHandler\DataRetention\GetProcessedList::class,
    Query\DataRetention\Postcheck::class => QueryHandler\DataRetention\Postcheck::class,

    // Sla Target Dates
    TransferQuery\System\SlaTargetDate::class => QueryHandler\System\SlaTargetDate::class,

    // Decisions
    TransferQuery\Decision\DecisionList::class => QueryHandler\Decision\DecisionList::class,

    // Reasons
    TransferQuery\Reason\ReasonList::class => QueryHandler\Reason\ReasonList::class,

    TransferQuery\TaskAllocationRule\GetList::class => QueryHandler\TaskAllocationRule\GetList::class,
    TransferQuery\TaskAllocationRule\Get::class => QueryHandler\TaskAllocationRule\Get::class,

    TransferQuery\TaskAlphaSplit\Get::class => QueryHandler\TaskAlphaSplit\Get::class,
    TransferQuery\TaskAlphaSplit\GetList::class => QueryHandler\TaskAlphaSplit\GetList::class,

    // EventHistory
    TransferQuery\EventHistory\EventHistory::class => QueryHandler\EventHistory\EventHistory::class,

    // Admin :: System Messages
    TransferQuery\System\InfoMessage\Get::class => QueryHandler\System\InfoMessage\Get::class,
    TransferQuery\System\InfoMessage\GetList::class => QueryHandler\System\InfoMessage\GetList::class,
    TransferQuery\System\InfoMessage\GetListActive::class => QueryHandler\System\InfoMessage\GetListActive::class,

    // Admin :: Public holidays
    TransferQuery\System\PublicHoliday\Get::class => QueryHandler\System\PublicHoliday\Get::class,
    TransferQuery\System\PublicHoliday\GetList::class => QueryHandler\System\PublicHoliday\GetList::class,

    // Si
    TransferQuery\Si\SiCategoryTypeListData::class => QueryHandler\Si\SiCategoryTypeListData::class,
    TransferQuery\Si\SiPenaltyTypeListData::class => QueryHandler\Si\SiPenaltyTypeListData::class,

    // Data Service
    TransferQuery\DataService\ApplicationStatus::class => QueryHandler\DataService\ApplicationStatus::class,

    // Diagnostics
    Query\Diagnostics\CheckFkIntegrity::class => QueryHandler\Diagnostics\CheckFkIntegrity::class,
    Query\Diagnostics\GenerateCheckFkIntegritySql::class => QueryHandler\Diagnostics\GenerateCheckFkIntegritySql::class,

    // Permits
    TransferQuery\IrhpApplication\ById::class => QueryHandler\IrhpApplication\ById::class,
    TransferQuery\IrhpApplication\AvailableLicences::class => QueryHandler\IrhpApplication\AvailableLicences::class,
    TransferQuery\IrhpApplication\QuestionAnswer::class => QueryHandler\IrhpApplication\QuestionAnswer::class,
    TransferQuery\IrhpApplication\MaxStockPermits::class => QueryHandler\IrhpApplication\MaxStockPermits::class,
    TransferQuery\IrhpApplication\MaxStockPermitsByApplication::class =>
        QueryHandler\IrhpApplication\MaxStockPermitsByApplication::class,
    TransferQuery\IrhpApplication\FeeBreakdown::class => QueryHandler\IrhpApplication\FeeBreakdown::class,
    TransferQuery\IrhpApplication\FeePerPermit::class => QueryHandler\IrhpApplication\FeePerPermit::class,
    TransferQuery\IrhpApplication\ApplicationStep::class => QueryHandler\IrhpApplication\ApplicationStep::class,
    TransferQuery\IrhpApplication\ApplicationPath::class => QueryHandler\IrhpApplication\ApplicationPath::class,
    TransferQuery\IrhpApplication\Documents::class => QueryHandler\IrhpApplication\Documents::class,
    TransferQuery\IrhpApplication\ApplicationPathGroupList::class => QueryHandler\IrhpApplication\ApplicationPathGroupList::class,
    TransferQuery\IrhpApplication\AnswersSummary::class => QueryHandler\IrhpApplication\AnswersSummary::class,
    TransferQuery\IrhpApplication\PermitsAvailable::class => QueryHandler\IrhpApplication\PermitsAvailable::class,
    TransferQuery\IrhpApplication\RangesByIrhpApplication::class => QueryHandler\IrhpApplication\RangesByIrhpApplication::class,
    TransferQuery\IrhpApplication\GetGrantability::class => QueryHandler\IrhpApplication\GetGrantability::class,
    TransferQuery\IrhpApplication\InternalApplicationsSummary::class => QueryHandler\IrhpApplication\InternalApplicationsSummary::class,
    TransferQuery\IrhpApplication\SelfserveApplicationsSummary::class => QueryHandler\IrhpApplication\SelfserveApplicationsSummary::class,
    TransferQuery\IrhpApplication\SelfserveIssuedPermitsSummary::class => QueryHandler\IrhpApplication\SelfserveIssuedPermitsSummary::class,
    TransferQuery\IrhpApplication\BilateralCountryAccessible::class => QueryHandler\IrhpApplication\BilateralCountryAccessible::class,
    TransferQuery\IrhpApplication\BilateralMetadata::class => QueryHandler\IrhpApplication\BilateralMetadata::class,
    TransferQuery\Permits\Sectors::class => QueryHandler\Permits\Sectors::class,
    TransferQuery\Permits\EcmtPermitFees::class => QueryHandler\Permits\EcmtPermitFees::class,
    TransferQuery\IrhpPermitStock\AvailableCountries::class => QueryHandler\IrhpPermitStock\AvailableCountries::class,
    TransferQuery\Permits\AvailableTypes::class => QueryHandler\Permits\AvailableTypes::class,
    TransferQuery\Permits\AvailableYears::class => QueryHandler\Permits\AvailableYears::class,
    TransferQuery\Permits\AvailableStocks::class => QueryHandler\Permits\AvailableStocks::class,
    TransferQuery\Permits\MaxPermittedReachedByStockAndLicence::class => QueryHandler\Permits\MaxPermittedReachedByStockAndLicence::class,
    TransferQuery\Permits\MaxPermittedReachedByTypeAndOrganisation::class => QueryHandler\Permits\MaxPermittedReachedByTypeAndOrganisation::class,
    TransferQuery\Permits\EmissionsByYear::class => QueryHandler\Permits\EmissionsByYear::class,
    TransferQuery\Permits\OpenWindows::class => QueryHandler\Permits\OpenWindows::class,
    TransferQuery\Permits\StockOperationsPermitted::class => QueryHandler\Permits\StockOperationsPermitted::class,
    TransferQuery\Permits\StockAlignmentReport::class => QueryHandler\Permits\StockAlignmentReport::class,
    TransferQuery\Permits\PostScoringReport::class => QueryHandler\Permits\PostScoringReport::class,
    TransferQuery\Permits\ReportList::class => QueryHandler\Permits\Report\ReportList::class,

    // Permits - internal
    Query\Permits\QueueRunScoringPermitted::class => QueryHandler\Permits\QueueRunScoringPermitted::class,
    Query\Permits\QueueAcceptScoringAndPostScoringReportPermitted::class =>
        QueryHandler\Permits\QueueAcceptScoringAndPostScoringReportPermitted::class,
    Query\Permits\CheckRunScoringPrerequisites::class => QueryHandler\Permits\CheckRunScoringPrerequisites::class,
    Query\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites::class =>
        QueryHandler\Permits\CheckAcceptScoringAndPostScoringReportPrerequisites::class,
    Query\Permits\GetScoredPermitList::class => QueryHandler\Permits\GetScoredPermitList::class,
    Query\Permits\DeviationData::class => QueryHandler\Permits\DeviationData::class,

    // IRHP Permit - internal
    Query\IrhpPermit\ByPermitNumber::class => QueryHandler\IrhpPermit\ByPermitNumber::class,
    Query\IrhpPermitRange\ByPermitNumber::class => QueryHandler\IrhpPermitRange\ByPermitNumber::class,

    // Digital Surrender
    TransferQuery\Surrender\GetSignature::class => QueryHandler\Surrender\GetSignature::class,
    TransferQuery\Surrender\ByLicence::class => QueryHandler\Surrender\ByLicence::class,
    TransferQuery\Surrender\OpenBusReg::class => QueryHandler\Surrender\OpenBusReg::class,
    TransferQuery\Surrender\OpenCases::class => QueryHandler\Surrender\OpenCases::class,
    TransferQuery\Surrender\PreviousLicenceStatus::class => QueryHandler\Surrender\PreviousLicenceStatus::class,

    // Document Internal
    Query\Document\ByDocumentStoreId::class => QueryHandler\Document\ByDocumentStoreId::class,

    // Templates
    TransferQuery\Template\AvailableTemplates::class => QueryHandler\Template\AvailableTemplates::class,
    TransferQuery\Template\PreviewTemplateSource::class => QueryHandler\Template\PreviewTemplateSource::class,
    TransferQuery\Template\TemplateSource::class => QueryHandler\Template\TemplateSource::class,
    TransferQuery\Template\TemplateCategories::class => QueryHandler\Template\TemplateCategories::class,

    // Editable Translations
    TransferQuery\TranslationKey\ById::class => QueryHandler\TranslationKey\ById::class,
    TransferQuery\TranslationKey\GetList::class => QueryHandler\TranslationKey\GetList::class,
    TransferQuery\Language\GetList::class => QueryHandler\Language\GetList::class,

    // Replacements
    TransferQuery\Replacement\ById::class => QueryHandler\Replacement\ById::class,
    TransferQuery\Replacement\GetList::class => QueryHandler\Replacement\GetList::class,

    // DvlaSearch
    TransferQuery\DvlaSearch\Vehicle::class => QueryHandler\DvlaSearch\Vehicle::class,

    // Cache
    TransferQuery\Cache\ById::class                                                   => QueryHandler\Cache\ById::class,
    Query\Cache\Replacements::class                                                   => QueryHandler\Cache\Replacements::class,
    Query\Cache\TranslationKey::class                                                 => QueryHandler\Cache\TranslationKey::class,
    Query\Cache\Single::class                                                         => QueryHandler\Cache\Single::class,
    Query\Cache\RecordList::class                                                     => QueryHandler\Cache\RecordList::class,

    // Messaging
    TransferQuery\Messaging\Documents::class                                          => QueryHandler\Messaging\Documents::class,
    TransferQuery\Messaging\Conversations\ByLicence::class                            => QueryHandler\Messaging\Conversations\ByLicence::class,
    TransferQuery\Messaging\Conversations\ByCaseToLicence::class                      => QueryHandler\Messaging\Conversations\ByCaseToLicence::class,
    TransferQuery\Messaging\Conversations\ByOrganisation::class                       => QueryHandler\Messaging\Conversations\ByOrganisation::class,
    TransferQuery\Messaging\Conversations\ByApplicationToLicence::class               => QueryHandler\Messaging\Conversations\ByApplicationToLicence::class,
    TransferQuery\Messaging\Messages\ByConversation::class                            => QueryHandler\Messaging\Message\ByConversation::class,
    TransferQuery\Messaging\ApplicationLicenceList\ByOrganisation::class              => QueryHandler\Messaging\ApplicationLicenceList\ByOrganisation::class,
    TransferQuery\Messaging\ApplicationLicenceList\ByLicenceToOrganisation::class     => QueryHandler\Messaging\ApplicationLicenceList\ByLicenceToOrganisation::class,
    TransferQuery\Messaging\ApplicationLicenceList\ByApplicationToOrganisation::class => QueryHandler\Messaging\ApplicationLicenceList\ByApplicationToOrganisation::class,
    TransferQuery\Messaging\ApplicationLicenceList\ByCaseToOrganisation::class        => QueryHandler\Messaging\ApplicationLicenceList\ByCaseToOrganisation::class,
    TransferQuery\Messaging\Subjects\All::class                                       => QueryHandler\Messaging\Subjects\All::class,
    TransferQuery\Messaging\Messages\UnreadCountByOrganisationAndUser::class          => QueryHandler\Messaging\Message\UnreadCountByOrganisationAndRoles::class,
    TransferQuery\Messaging\Messages\UnreadCountByLicenceAndRoles::class              => QueryHandler\Messaging\Message\UnreadCountByLicenceAndRoles::class,

    // Letter
    TransferQuery\Letter\LetterType\Get::class => QueryHandler\Letter\LetterType\Get::class,
    TransferQuery\Letter\LetterType\GetList::class => QueryHandler\Letter\LetterType\GetList::class,
    TransferQuery\Letter\LetterSection\Get::class => QueryHandler\Letter\LetterSection\Get::class,
    TransferQuery\Letter\LetterSection\GetList::class => QueryHandler\Letter\LetterSection\GetList::class,
    TransferQuery\Letter\LetterIssue\Get::class => QueryHandler\Letter\LetterIssue\Get::class,
    TransferQuery\Letter\LetterIssue\GetList::class => QueryHandler\Letter\LetterIssue\GetList::class,
    TransferQuery\Letter\LetterIssueType\Get::class => QueryHandler\Letter\LetterIssueType\Get::class,
    TransferQuery\Letter\LetterIssueType\GetList::class => QueryHandler\Letter\LetterIssueType\GetList::class,
    TransferQuery\Letter\LetterTodo\Get::class => QueryHandler\Letter\LetterTodo\Get::class,
    TransferQuery\Letter\LetterTodo\GetList::class => QueryHandler\Letter\LetterTodo\GetList::class,
    TransferQuery\Letter\LetterAppendix\Get::class => QueryHandler\Letter\LetterAppendix\Get::class,
    TransferQuery\Letter\LetterAppendix\GetList::class => QueryHandler\Letter\LetterAppendix\GetList::class,
    TransferQuery\Letter\LetterInstance\Get::class => QueryHandler\Letter\LetterInstance\Get::class,
    TransferQuery\Letter\LetterInstance\GetList::class => QueryHandler\Letter\LetterInstance\GetList::class,
    TransferQuery\Letter\LetterInstance\Preview::class => QueryHandler\Letter\LetterInstance\Preview::class,
    TransferQuery\Letter\MasterTemplate\Get::class => QueryHandler\Letter\MasterTemplate\Get::class,
    TransferQuery\Letter\MasterTemplate\GetList::class => QueryHandler\Letter\MasterTemplate\GetList::class,
    TransferQuery\Letter\LetterTestData\Get::class => QueryHandler\Letter\LetterTestData\Get::class,
    TransferQuery\Letter\LetterTestData\GetList::class => QueryHandler\Letter\LetterTestData\GetList::class,
];
