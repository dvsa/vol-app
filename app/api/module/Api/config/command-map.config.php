<?php

use Dvsa\Olcs\Api\Domain\Command;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion as AppCompCommand;
use Dvsa\Olcs\Api\Domain\Command\Queue as QueueCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion as AppCompCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Queue as QueueCommandHandler;
use Dvsa\Olcs\Cli\Domain\Command as CommandCli;
use Dvsa\Olcs\Cli\Domain\CommandHandler as CommandHandlerCli;
use Dvsa\Olcs\Transfer\Command as TransferCommand;

return [
    // Transfer - Auth
    TransferCommand\Auth\Login::class => CommandHandler\Auth\LoginFactory::class,
    TransferCommand\Auth\ChangePassword::class => CommandHandler\Auth\ChangePasswordFactory::class,
    TransferCommand\Auth\RefreshTokens::class => CommandHandler\Auth\RefreshTokensFactory::class,
    TransferCommand\Auth\ChangeExpiredPassword::class => CommandHandler\Auth\ChangeExpiredPasswordFactory::class,
    TransferCommand\Auth\ResetPassword::class => CommandHandler\Auth\ResetPasswordFactory::class,
    TransferCommand\Auth\ForgotPassword::class => CommandHandler\Auth\ForgotPasswordFactory::class,

    // Transfer - Audit
    TransferCommand\Audit\ReadApplication::class => CommandHandler\Audit\ReadApplication::class,
    TransferCommand\Audit\ReadLicence::class => CommandHandler\Audit\ReadLicence::class,
    TransferCommand\Audit\ReadOrganisation::class => CommandHandler\Audit\ReadOrganisation::class,
    TransferCommand\Audit\ReadTransportManager::class => CommandHandler\Audit\ReadTransportManager::class,
    TransferCommand\Audit\ReadBusReg::class => CommandHandler\Audit\ReadBusReg::class,
    TransferCommand\Audit\ReadCase::class => CommandHandler\Audit\ReadCase::class,
    TransferCommand\Audit\ReadIrhpApplication::class => CommandHandler\Audit\ReadIrhpApplication::class,

    // Transfer - Application
    TransferCommand\Application\UpdateTypeOfLicence::class => CommandHandler\Application\UpdateTypeOfLicence::class,
    TransferCommand\Application\CreateApplication::class => CommandHandler\Application\CreateApplication::class,
    TransferCommand\Application\UpdateFinancialHistory::class =>
        CommandHandler\Application\UpdateFinancialHistory::class,
    TransferCommand\Application\UpdateFinancialEvidence::class =>
        CommandHandler\Application\UpdateFinancialEvidence::class,
    TransferCommand\Application\UpdateLicenceHistory::class => CommandHandler\Application\UpdateLicenceHistory::class,
    TransferCommand\Application\UpdatePreviousConvictions::class =>
        CommandHandler\Application\UpdatePreviousConvictions::class,
    TransferCommand\Application\UpdateDeclaration::class => CommandHandler\Application\UpdateDeclaration::class,
    TransferCommand\Application\UpdateBusinessDetails::class => CommandHandler\Application\UpdateBusinessDetails::class,
    TransferCommand\Application\UpdateCompanySubsidiary::class =>
        CommandHandler\Application\UpdateCompanySubsidiary::class,
    TransferCommand\Application\CreateCompanySubsidiary::class =>
        CommandHandler\Application\CreateCompanySubsidiary::class,
    TransferCommand\Application\DeleteCompanySubsidiary::class =>
        CommandHandler\Application\DeleteCompanySubsidiary::class,
    TransferCommand\Application\UpdateAddresses::class => CommandHandler\Application\UpdateAddresses::class,
    TransferCommand\Application\UpdateSafety::class => CommandHandler\Application\UpdateSafety::class,
    TransferCommand\Application\DeleteWorkshop::class => CommandHandler\Application\DeleteWorkshop::class,
    TransferCommand\Application\CreateWorkshop::class => CommandHandler\Application\CreateWorkshop::class,
    TransferCommand\Application\UpdateWorkshop::class => CommandHandler\Application\UpdateWorkshop::class,
    TransferCommand\Application\SubmitApplication::class => CommandHandler\Application\SubmitApplication::class,
    TransferCommand\Application\UpdateVehicles::class => CommandHandler\Application\UpdateVehicles::class,
    TransferCommand\Application\CreateGoodsVehicle::class => CommandHandler\Application\CreateGoodsVehicle::class,
    TransferCommand\Application\UpdateGoodsVehicle::class => CommandHandler\Application\UpdateGoodsVehicle::class,
    TransferCommand\Application\DeleteGoodsVehicle::class => CommandHandler\Application\DeleteGoodsVehicle::class,
    TransferCommand\Application\DeletePsvVehicle::class => CommandHandler\Application\DeletePsvVehicle::class,
    TransferCommand\Application\CreateVehicleListDocument::class
        => CommandHandler\Application\CreateVehicleListDocument::class,
    TransferCommand\Application\UpdateVehicleSize::class =>
        CommandHandler\Application\UpdateVehicleSize::class,
    TransferCommand\Application\UpdateMainOccupationEvidence::class =>
        CommandHandler\Application\UpdateMainOccupationEvidence::class,
    TransferCommand\Application\UpdateSmallVehicleEvidence::class =>
        CommandHandler\Application\UpdateSmallVehicleEvidence::class,
    TransferCommand\Application\UpdateMainOccupationUndertakings::class =>
        CommandHandler\Application\UpdateMainOccupationUndertakings::class,
    TransferCommand\Application\UpdateNoveltyVehicles::class =>
        CommandHandler\Application\UpdateNoveltyVehicles::class,
    TransferCommand\Application\UpdateSmallVehicleConditionsAndUndertaking::class =>
        CommandHandler\Application\UpdateSmallVehicleConditionsAndUndertaking::class,
    TransferCommand\Application\UpdateVehicleOperatingSmall::class =>
        CommandHandler\Application\UpdateVehicleOperatingSmall::class,
    TransferCommand\Application\UpdateVehicleNinePassengers::class =>
        CommandHandler\Application\UpdateVehicleNinePassengers::class,
    TransferCommand\Application\UpdateWrittenExplanation::class =>
        CommandHandler\Application\UpdateWrittenExplanation::class,
    TransferCommand\Application\WithdrawApplication::class => CommandHandler\Application\WithdrawApplication::class,
    TransferCommand\Application\ReviveApplication::class => CommandHandler\Application\ReviveApplication::class,
    TransferCommand\Application\RefuseApplication::class => CommandHandler\Application\RefuseApplication::class,
    TransferCommand\Application\NotTakenUpApplication::class => CommandHandler\Application\NotTakenUpApplication::class,
    TransferCommand\Application\Overview::class => CommandHandler\Application\Overview::class,
    TransferCommand\Application\CreateSnapshot::class => CommandHandler\Application\CreateSnapshot::class,
    TransferCommand\Application\Grant::class => CommandHandler\Application\Grant::class,
    TransferCommand\Application\UndoGrant::class => CommandHandler\Application\UndoGrant::class,
    TransferCommand\Application\CancelApplication::class => CommandHandler\Application\CancelApplication::class,
    Command\Application\GrantGoods::class => CommandHandler\Application\GrantGoods::class,
    Command\Application\GrantPsv::class => CommandHandler\Application\GrantPsv::class,
    Command\Application\CreateGrantFee::class => CommandHandler\Application\CreateGrantFee::class,
    Command\Application\Grant\CreateDiscRecords::class => CommandHandler\Application\Grant\CreateDiscRecords::class,
    Command\Application\Grant\CopyApplicationDataToLicence::class
        => CommandHandler\Application\Grant\CopyApplicationDataToLicence::class,
    Command\Application\Grant\ProcessApplicationOperatingCentres::class
        => CommandHandler\Application\Grant\ProcessApplicationOperatingCentres::class,
    Command\Application\Grant\CommonGrant::class => CommandHandler\Application\Grant\CommonGrant::class,
    Command\Application\Grant\GrantConditionUndertaking::class
        => CommandHandler\Application\Grant\GrantConditionUndertaking::class,
    Command\Application\Grant\GrantCommunityLicence::class
        => CommandHandler\Application\Grant\GrantCommunityLicence::class,
    Command\Application\Grant\GrantTransportManager::class
        => CommandHandler\Application\Grant\GrantTransportManager::class,
    Command\Application\Grant\GrantPeople::class => CommandHandler\Application\Grant\GrantPeople::class,
    Command\Application\Grant\CreatePostDeletePeopleGrantTask::class
        => CommandHandler\Application\Grant\CreatePostDeletePeopleGrantTask::class,
    Command\Application\Grant\CreatePostAddPeopleGrantTask::class
        => CommandHandler\Application\Grant\CreatePostAddPeopleGrantTask::class,
    Command\Application\Grant\ValidateApplication::class => CommandHandler\Application\Grant\ValidateApplication::class,
    Command\Application\Grant\Schedule41::class => CommandHandler\Application\Grant\Schedule41::class,
    Command\Application\Grant\ProcessDuplicateVehicles::class
        => CommandHandler\Application\Grant\ProcessDuplicateVehicles::class,
    TransferCommand\Application\CreatePeople::class => CommandHandler\Application\CreatePeople::class,
    TransferCommand\Application\UpdatePeople::class => CommandHandler\Application\UpdatePeople::class,
    TransferCommand\Application\DeletePeople::class => CommandHandler\Application\DeletePeople::class,
    TransferCommand\Application\RestorePeople::class => CommandHandler\Application\RestorePeople::class,
    TransferCommand\Application\UpdateCompletion::class =>
        CommandHandler\Application\UpdateApplicationCompletion::class,
    TransferCommand\Application\Schedule41::class => CommandHandler\Application\Schedule41::class,
    TransferCommand\Application\Schedule41Approve::class => CommandHandler\Application\Schedule41Approve::class,
    TransferCommand\Application\Schedule41Reset::class => CommandHandler\Application\Schedule41Reset::class,
    TransferCommand\Application\Schedule41Refuse::class => CommandHandler\Application\Schedule41Refuse::class,
    TransferCommand\Application\Schedule41Cancel::class => CommandHandler\Application\Schedule41Cancel::class,
    TransferCommand\Application\PrintInterimDocument::class => CommandHandler\Application\PrintInterimDocument::class,
    TransferCommand\Application\UpdateInterim::class => CommandHandler\Application\UpdateInterim::class,
    TransferCommand\Application\RefuseInterim::class => CommandHandler\Application\RefuseInterim::class,
    TransferCommand\Application\GrantInterim::class => CommandHandler\Application\GrantInterim::class,
    Command\Application\InForceInterim::class => CommandHandler\Application\InForceInterim::class,
    Command\Application\EndInterim::class => CommandHandler\Application\EndInterim::class,
    TransferCommand\Application\UpdateOperatingCentres::class
        => CommandHandler\Application\UpdateOperatingCentres::class,
    TransferCommand\Application\DeleteOperatingCentres::class
        => CommandHandler\Application\DeleteOperatingCentres::class,
    Command\Application\HandleOcVariationFees::class => CommandHandler\Application\HandleOcVariationFees::class,
    TransferCommand\Application\CreateOperatingCentre::class => CommandHandler\Application\CreateOperatingCentre::class,
    TransferCommand\Application\CreateTaxiPhv::class => CommandHandler\Application\CreateTaxiPhv::class,
    TransferCommand\Application\UpdatePrivateHireLicence::class =>
        CommandHandler\Application\UpdatePrivateHireLicence::class,
    TransferCommand\Application\DeleteTaxiPhv::class => CommandHandler\Application\DeleteTaxiPhv::class,
    TransferCommand\Application\UpdateTaxiPhv::class => CommandHandler\Application\UpdateTaxiPhv::class,
    Command\Application\CreateTexTask::class => CommandHandler\Application\CreateTexTask::class,
    Command\Application\CloseTexTask::class => CommandHandler\Application\CloseTexTask::class,
    Command\Application\CloseFeeDueTask::class => CommandHandler\Application\CloseFeeDueTask::class,
    TransferCommand\Application\UpdateAuthSignature::class => CommandHandler\Application\UpdateAuthSignature::class,
    TransferCommand\Application\Publish::class => CommandHandler\Application\Publish::class,
    TransferCommand\Application\UploadEvidence::class => CommandHandler\Application\UploadEvidence::class,

    Command\Task\CreateTranslateToWelshTask::class => CommandHandler\Task\CreateTranslateToWelshTask::class,
    TransferCommand\Application\UpdatePsvVehicles::class => CommandHandler\Application\UpdatePsvVehicles::class,
    TransferCommand\Application\CreatePsvVehicle::class => CommandHandler\Application\CreatePsvVehicle::class,

    // Transfer - Workshop
    TransferCommand\Workshop\DeleteWorkshop::class => CommandHandler\Workshop\DeleteWorkshop::class,
    TransferCommand\Workshop\CreateWorkshop::class => CommandHandler\Workshop\CreateWorkshop::class,
    TransferCommand\Workshop\UpdateWorkshop::class => CommandHandler\Workshop\UpdateWorkshop::class,

    // Transfer - Note
    TransferCommand\Processing\Note\Create::class => CommandHandler\Processing\Note\Create::class,
    TransferCommand\Processing\Note\Update::class => CommandHandler\Processing\Note\Update::class,
    TransferCommand\Processing\Note\Delete::class => CommandHandler\Processing\Note\Delete::class,

    // Non Pi
    TransferCommand\Cases\NonPi\Create::class => CommandHandler\Cases\NonPi\Create::class,
    TransferCommand\Cases\NonPi\Update::class => CommandHandler\Cases\NonPi\Update::class,
    TransferCommand\Cases\NonPi\Delete::class => CommandHandler\Cases\NonPi\Delete::class,

    // Pi
    TransferCommand\Cases\Pi\CreateAgreedAndLegislation::class =>
        CommandHandler\Cases\Pi\CreateAgreedAndLegislation::class,
    TransferCommand\Cases\Pi\UpdateAgreedAndLegislation::class =>
        CommandHandler\Cases\Pi\AgreedAndLegislationUpdate::class,
    TransferCommand\Cases\Pi\UpdateDecision::class => CommandHandler\Cases\Pi\UpdateDecision::class,
    TransferCommand\Cases\Pi\UpdateTmDecision::class => CommandHandler\Cases\Pi\UpdateTmDecision::class,
    TransferCommand\Cases\Pi\UpdateSla::class => CommandHandler\Cases\Pi\UpdateSla::class,
    TransferCommand\Cases\Pi\CreateHearing::class => CommandHandler\Cases\Pi\CreateHearing::class,
    TransferCommand\Cases\Pi\UpdateHearing::class => CommandHandler\Cases\Pi\UpdateHearing::class,
    TransferCommand\Cases\Pi\Close::class => CommandHandler\Cases\Pi\Close::class,
    TransferCommand\Cases\Pi\Reopen::class => CommandHandler\Cases\Pi\Reopen::class,
    TransferCommand\Cases\Pi\CreatePiSlaException::class => CommandHandler\Cases\Pi\CreateSlaException::class,

    // Transfer - Si, Erru
    TransferCommand\Cases\Si\Applied\Delete::class => CommandHandler\Cases\Si\Applied\Delete::class,
    TransferCommand\Cases\Si\Applied\Create::class => CommandHandler\Cases\Si\Applied\Create::class,
    TransferCommand\Cases\Si\Applied\Update::class => CommandHandler\Cases\Si\Applied\Update::class,
    TransferCommand\Cases\Si\CreateSi::class => CommandHandler\Cases\Si\CreateSi::class,
    TransferCommand\Cases\Si\DeleteSi::class => CommandHandler\Cases\Si\DeleteSi::class,
    TransferCommand\Cases\Si\UpdateSi::class => CommandHandler\Cases\Si\UpdateSi::class,
    TransferCommand\Cases\Si\CreateResponse::class => CommandHandler\Cases\Si\CreateResponse::class,
    TransferCommand\Cases\Si\ComplianceEpisode::class => CommandHandler\Cases\Si\ComplianceEpisodeDocument::class,

    // Transfer - Cases - Presiding TM

    TransferCommand\Cases\PresidingTc\Create::class => CommandHandler\Cases\PresidingTc\Create::class,
    TransferCommand\Cases\PresidingTc\Update::class => CommandHandler\Cases\PresidingTc\Update::class,
    TransferCommand\Cases\PresidingTc\Delete::class => CommandHandler\Cases\PresidingTc\Delete::class,

    // Transfer - Bus
    TransferCommand\Bus\CreateBus::class => CommandHandler\Bus\CreateBus::class,
    TransferCommand\Bus\CreateNoticePeriod::class => CommandHandler\Bus\CreateNoticePeriod::class,
    TransferCommand\Bus\CreateVariation::class => CommandHandler\Bus\CreateVariation::class,
    TransferCommand\Bus\CreateCancellation::class => CommandHandler\Bus\CreateCancellation::class,
    TransferCommand\Bus\UpdateStops::class => CommandHandler\Bus\UpdateStops::class,
    TransferCommand\Bus\UpdateQualitySchemes::class => CommandHandler\Bus\UpdateQualitySchemes::class,
    TransferCommand\Bus\UpdateTaAuthority::class => CommandHandler\Bus\UpdateTaAuthority::class,
    TransferCommand\Bus\UpdateServiceDetails::class => CommandHandler\Bus\UpdateServiceDetails::class,
    TransferCommand\Bus\UpdateShortNotice::class => CommandHandler\Bus\UpdateShortNotice::class,
    TransferCommand\Bus\UpdateServiceRegister::class => CommandHandler\Bus\UpdateServiceRegister::class,
    TransferCommand\Bus\DeleteBus::class => CommandHandler\Bus\DeleteBus::class,
    TransferCommand\Bus\ResetBusReg::class => CommandHandler\Bus\ResetBusReg::class,
    TransferCommand\Bus\AdminCancelBusReg::class => CommandHandler\Bus\AdminCancelBusReg::class,
    TransferCommand\Bus\WithdrawBusReg::class => CommandHandler\Bus\WithdrawBusReg::class,
    TransferCommand\Bus\RefuseBusReg::class => CommandHandler\Bus\RefuseBusReg::class,
    TransferCommand\Bus\RefuseBusRegByShortNotice::class => CommandHandler\Bus\RefuseBusRegByShortNotice::class,
    TransferCommand\Bus\GrantBusReg::class => CommandHandler\Bus\GrantBusReg::class,

    //Ebsr
    TransferCommand\Bus\Ebsr\UpdateTxcInbox::class => CommandHandler\Bus\Ebsr\UpdateTxcInbox::class,
    Command\Bus\Ebsr\CreateTxcInbox::class => CommandHandler\Bus\Ebsr\CreateTxcInbox::class,
    Command\Bus\Ebsr\UpdateTxcInboxPdf::class => CommandHandler\Bus\Ebsr\UpdateTxcInboxPdf::class,
    Command\Bus\Ebsr\ProcessPack::class => CommandHandler\Bus\Ebsr\ProcessPack::class,
    Command\Bus\Ebsr\ProcessPackTransaction::class => CommandHandler\Bus\Ebsr\ProcessPackTransaction::class,
    Command\Bus\Ebsr\ProcessPackFailed::class => CommandHandler\Bus\Ebsr\ProcessPackFailed::class,
    TransferCommand\Bus\Ebsr\RequestMap::class => CommandHandler\Bus\Ebsr\RequestMapQueue::class,
    TransferCommand\Bus\Ebsr\QueuePacks::class => CommandHandler\Bus\Ebsr\QueuePacks::class,
    Command\Bus\Ebsr\ProcessRequestMap::class => CommandHandler\Bus\Ebsr\ProcessRequestMap::class,
    Command\Bus\Ebsr\CreateSubmission::class => CommandHandler\Bus\Ebsr\CreateSubmission::class,
    Command\Bus\Ebsr\DeleteSubmission::class => CommandHandler\Bus\Ebsr\DeleteSubmission::class,
    TransferCommand\Bus\PrintLetter::class => CommandHandler\Bus\PrintLetter::class,

    // Transfer - Licence
    TransferCommand\Licence\UpdateAddresses::class => CommandHandler\Licence\UpdateAddresses::class,
    TransferCommand\Licence\UpdateBusinessDetails::class => CommandHandler\Licence\UpdateBusinessDetails::class,
    TransferCommand\Licence\UpdateCompanySubsidiary::class => CommandHandler\Licence\UpdateCompanySubsidiary::class,
    TransferCommand\Licence\CreateCompanySubsidiary::class => CommandHandler\Licence\CreateCompanySubsidiary::class,
    TransferCommand\Licence\DeleteCompanySubsidiary::class => CommandHandler\Licence\DeleteCompanySubsidiary::class,
    TransferCommand\Licence\UpdateSafety::class => CommandHandler\Licence\UpdateSafety::class,
    TransferCommand\Licence\CreateGoodsVehicle::class => CommandHandler\Licence\CreateGoodsVehicle::class,
    TransferCommand\Licence\CreateVehicleListDocument::class => CommandHandler\Licence\CreateVehicleListDocument::class,
    TransferCommand\Licence\TransferVehicles::class => CommandHandler\Licence\TransferVehicles::class,
    TransferCommand\Licence\PrintLicence::class => CommandHandler\Licence\PrintLicence::class,
    TransferCommand\Licence\CreatePeople::class => CommandHandler\Licence\CreatePeople::class,
    TransferCommand\Licence\UpdatePeople::class => CommandHandler\Licence\UpdatePeople::class,
    TransferCommand\Licence\DeletePeople::class => CommandHandler\Licence\DeletePeople::class,
    TransferCommand\Licence\DeletePeopleViaVariation::class => CommandHandler\Licence\DeletePeopleViaVariation::class,
    Command\Document\DispatchDocument::class => CommandHandler\Document\DispatchDocument::class,
    TransferCommand\Licence\Overview::class => CommandHandler\Licence\Overview::class,
    TransferCommand\Licence\UpdateTrafficArea::class => CommandHandler\Licence\UpdateTrafficArea::class,
    Command\Licence\VoidAllCommunityLicences::class => CommandHandler\Licence\VoidAllCommunityLicences::class,
    TransferCommand\Licence\ContinueLicence::class => CommandHandler\Licence\ContinueLicence::class,
    TransferCommand\Licence\DeleteOperatingCentres::class => CommandHandler\Licence\DeleteOperatingCentres::class,
    TransferCommand\Licence\CreateOperatingCentre::class => CommandHandler\Licence\CreateOperatingCentre::class,
    TransferCommand\Licence\UpdateOperatingCentres::class => CommandHandler\Licence\UpdateOperatingCentres::class,
    TransferCommand\Licence\CreatePsvVehicle::class => CommandHandler\Licence\CreatePsvVehicle::class,
    Command\Licence\ReturnAllCommunityLicences::class => CommandHandler\Licence\ReturnAllCommunityLicences::class,
    Command\Licence\ExpireAllCommunityLicences::class => CommandHandler\Licence\ExpireAllCommunityLicences::class,
    Command\Licence\EndIrhpApplicationsAndPermits::class => CommandHandler\Licence\EndIrhpApplicationsAndPermits::class,
    Command\Licence\EndIrhpApplications::class => CommandHandler\Licence\EndIrhpApplications::class,
    Command\Licence\EndIrhpPermits::class => CommandHandler\Licence\EndIrhpPermits::class,
    TransferCommand\Licence\UpdateTrailers::class => CommandHandler\Licence\UpdateTrailers::class,
    TransferCommand\Licence\UpdateVehicles::class => CommandHandler\Licence\UpdateVehicles::class,
    Command\Licence\TmNominatedTask::class => CommandHandler\Licence\TmNominatedTask::class,
    Command\Licence\BatchVehicleListGeneratorForGoodsDiscs::class =>
        CommandHandler\Licence\BatchVehicleListGeneratorForGoodsDiscs::class,
    Command\Discs\BatchVehicleListGeneratorForPsvDiscs::class =>
        CommandHandler\Discs\BatchVehicleListGeneratorForPsvDiscs::class,
    Command\Licence\Withdraw::class => CommandHandler\Licence\Withdraw::class,
    Command\Licence\Grant::class => CommandHandler\Licence\Grant::class,
    Command\Licence\Refuse::class => CommandHandler\Licence\Refuse::class,
    Command\Licence\NotTakenUp::class => CommandHandler\Licence\NotTakenUp::class,
    Command\Licence\UnderConsideration::class => CommandHandler\Licence\UnderConsideration::class,
    TransferCommand\Licence\ProposeToRevoke::class => CommandHandler\Licence\ProposeToRevoke::class,

    TransferCommand\Licence\CreatePsvDiscs::class => CommandHandler\Licence\CreatePsvDiscs::class,
    TransferCommand\Licence\VoidPsvDiscs::class => CommandHandler\Licence\VoidPsvDiscs::class,
    TransferCommand\Licence\ReplacePsvDiscs::class => CommandHandler\Licence\ReplacePsvDiscs::class,
    TransferCommand\Licence\CreateVariation::class => CommandHandler\Licence\CreateVariation::class,

    // Transfer - Variation
    TransferCommand\Variation\UpdateTypeOfLicence::class => CommandHandler\Variation\UpdateTypeOfLicence::class,
    TransferCommand\Variation\UpdateAddresses::class => CommandHandler\Variation\UpdateAddresses::class,
    TransferCommand\Variation\TransportManagerDeleteDelta::class
        => CommandHandler\Variation\TransportManagerDeleteDelta::class,
    TransferCommand\Variation\CreatePsvDiscs::class => CommandHandler\Variation\CreatePsvDiscs::class,
    TransferCommand\Variation\VoidPsvDiscs::class => CommandHandler\Variation\VoidPsvDiscs::class,
    TransferCommand\Variation\ReplacePsvDiscs::class => CommandHandler\Variation\ReplacePsvDiscs::class,
    TransferCommand\Variation\Grant::class => CommandHandler\Variation\Grant::class,
    TransferCommand\Variation\DeleteListConditionUndertaking::class =>
        CommandHandler\Variation\DeleteListConditionUndertaking::class,
    TransferCommand\Variation\UpdateConditionUndertaking::class =>
        CommandHandler\Variation\UpdateConditionUndertaking::class,
    TransferCommand\Variation\RestoreListConditionUndertaking::class =>
        CommandHandler\Variation\RestoreListConditionUndertaking::class,
    TransferCommand\Variation\DeleteOperatingCentre::class => CommandHandler\Variation\DeleteOperatingCentre::class,
    TransferCommand\Variation\RestoreOperatingCentre::class => CommandHandler\Variation\RestoreOperatingCentre::class,
    TransferCommand\Variation\UpdateInterim::class => CommandHandler\Variation\UpdateInterim::class,

    // Transfer - Organisation
    TransferCommand\Organisation\GenerateName::class => CommandHandler\Organisation\GenerateName::class,
    TransferCommand\Organisation\UpdateBusinessType::class => CommandHandler\Organisation\UpdateBusinessType::class,
    TransferCommand\Organisation\TransferTo::class => CommandHandler\Organisation\TransferTo::class,
    Command\Organisation\ChangeBusinessType::class => CommandHandler\Organisation\ChangeBusinessType::class,
    Command\Organisation\FixIsIrfo::class => CommandHandler\Organisation\FixIsIrfo::class,
    Command\Organisation\FixIsUnlicenced::class => CommandHandler\Organisation\FixIsUnlicenced::class,

    // Transfer - OtherLicence
    TransferCommand\OtherLicence\UpdateOtherLicence::class => CommandHandler\OtherLicence\UpdateOtherLicence::class,
    TransferCommand\OtherLicence\CreateOtherLicence::class => CommandHandler\OtherLicence\CreateOtherLicence::class,
    TransferCommand\OtherLicence\DeleteOtherLicence::class => CommandHandler\OtherLicence\DeleteOtherLicence::class,
    TransferCommand\OtherLicence\CreateForTma::class => CommandHandler\OtherLicence\CreateForTma::class,
    TransferCommand\OtherLicence\UpdateForTma::class => CommandHandler\OtherLicence\UpdateForTma::class,
    TransferCommand\OtherLicence\CreatePreviousLicence::class
        => CommandHandler\OtherLicence\CreatePreviousLicence::class,
    TransferCommand\OtherLicence\CreateForTm::class => CommandHandler\OtherLicence\CreateForTm::class,
    TransferCommand\OtherLicence\CreateForTml::class => CommandHandler\OtherLicence\CreateForTml::class,

    // Transfer - Previous Conviction
    TransferCommand\PreviousConviction\CreatePreviousConviction::class =>
        CommandHandler\PreviousConviction\CreatePreviousConviction::class,
    TransferCommand\PreviousConviction\UpdatePreviousConviction::class =>
        CommandHandler\PreviousConviction\UpdatePreviousConviction::class,
    TransferCommand\PreviousConviction\DeletePreviousConviction::class =>
        CommandHandler\PreviousConviction\DeletePreviousConviction::class,
    TransferCommand\PreviousConviction\CreateForTma::class => CommandHandler\PreviousConviction\CreateForTma::class,

    // Transfer - Trailer
    TransferCommand\Trailer\CreateTrailer::class => CommandHandler\Trailer\CreateTrailer::class,
    TransferCommand\Trailer\UpdateTrailer::class => CommandHandler\Trailer\UpdateTrailer::class,
    TransferCommand\Trailer\DeleteTrailer::class => CommandHandler\Trailer\DeleteTrailer::class,

    // Transfer - Grace Period
    TransferCommand\GracePeriod\CreateGracePeriod::class => CommandHandler\GracePeriod\CreateGracePeriod::class,
    TransferCommand\GracePeriod\UpdateGracePeriod::class => CommandHandler\GracePeriod\UpdateGracePeriod::class,
    TransferCommand\GracePeriod\DeleteGracePeriod::class => CommandHandler\GracePeriod\DeleteGracePeriod::class,

    // Transfer - Correspondence
    TransferCommand\Correspondence\AccessCorrespondence::class
        => CommandHandler\Correspondence\AccessCorrespondence::class,

    // Domain - Correspondence
    Command\Correspondence\ProcessInboxDocuments::class => CommandHandler\Correspondence\ProcessInboxDocuments::class,

    // Transfer - IRFO
    TransferCommand\Irfo\UpdateIrfoDetails::class => CommandHandler\Irfo\UpdateIrfoDetails::class,
    TransferCommand\Irfo\CreateIrfoGvPermit::class => CommandHandler\Irfo\CreateIrfoGvPermit::class,
    TransferCommand\Irfo\UpdateIrfoGvPermit::class => CommandHandler\Irfo\UpdateIrfoGvPermit::class,
    TransferCommand\Irfo\ResetIrfoGvPermit::class => CommandHandler\Irfo\ResetIrfoGvPermit::class,
    TransferCommand\Irfo\ApproveIrfoGvPermit::class => CommandHandler\Irfo\ApproveIrfoGvPermit::class,
    TransferCommand\Irfo\GenerateIrfoGvPermit::class => CommandHandler\Irfo\GenerateIrfoGvPermit::class,
    TransferCommand\Irfo\WithdrawIrfoGvPermit::class => CommandHandler\Irfo\WithdrawIrfoGvPermit::class,
    TransferCommand\Irfo\RefuseIrfoGvPermit::class => CommandHandler\Irfo\RefuseIrfoGvPermit::class,
    TransferCommand\Irfo\CreateIrfoPermitStock::class => CommandHandler\Irfo\CreateIrfoPermitStock::class,
    TransferCommand\Irfo\UpdateIrfoPermitStock::class => CommandHandler\Irfo\UpdateIrfoPermitStock::class,
    TransferCommand\Irfo\UpdateIrfoPermitStockIssued::class => CommandHandler\Irfo\UpdateIrfoPermitStockIssued::class,
    TransferCommand\Irfo\CreateIrfoPsvAuth::class => CommandHandler\Irfo\CreateIrfoPsvAuth::class,
    TransferCommand\Irfo\UpdateIrfoPsvAuth::class => CommandHandler\Irfo\UpdateIrfoPsvAuth::class,
    TransferCommand\Irfo\GrantIrfoPsvAuth::class => CommandHandler\Irfo\GrantIrfoPsvAuth::class,
    TransferCommand\Irfo\ApproveIrfoPsvAuth::class => CommandHandler\Irfo\ApproveIrfoPsvAuth::class,
    TransferCommand\Irfo\GenerateIrfoPsvAuth::class => CommandHandler\Irfo\GenerateIrfoPsvAuth::class,
    TransferCommand\Irfo\RefuseIrfoPsvAuth::class => CommandHandler\Irfo\RefuseIrfoPsvAuth::class,
    TransferCommand\Irfo\WithdrawIrfoPsvAuth::class => CommandHandler\Irfo\WithdrawIrfoPsvAuth::class,
    TransferCommand\Irfo\CnsIrfoPsvAuth::class => CommandHandler\Irfo\CnsIrfoPsvAuth::class,
    TransferCommand\Irfo\RenewIrfoPsvAuth::class => CommandHandler\Irfo\RenewIrfoPsvAuth::class,
    TransferCommand\Irfo\ResetIrfoPsvAuth::class => CommandHandler\Irfo\ResetIrfoPsvAuth::class,
    TransferCommand\Irfo\PrintIrfoPsvAuthChecklist::class => CommandHandler\Irfo\PrintIrfoPsvAuthChecklist::class,

    // Transfer - Publication
    TransferCommand\Publication\CreateRecipient::class => CommandHandler\Publication\CreateRecipient::class,
    TransferCommand\Publication\UpdateRecipient::class => CommandHandler\Publication\UpdateRecipient::class,
    TransferCommand\Publication\DeleteRecipient::class => CommandHandler\Publication\DeleteRecipient::class,
    TransferCommand\Publication\Publish::class => CommandHandler\Publication\Publish::class,
    TransferCommand\Publication\Generate::class => CommandHandler\Publication\Generate::class,
    TransferCommand\Publication\DeletePublicationLink::class => CommandHandler\Publication\DeletePublicationLink::class,
    TransferCommand\Publication\UpdatePublicationLink::class => CommandHandler\Publication\UpdatePublicationLink::class,

    // Transfer - My Account
    Command\MyAccount\UpdateMyAccount::class => CommandHandler\MyAccount\UpdateMyAccount::class,
    TransferCommand\MyAccount\UpdateMyAccount::class => CommandHandler\MyAccount\UpdateMyAccountInternal::class,
    TransferCommand\MyAccount\UpdateMyAccountSelfserve::class
        => CommandHandler\MyAccount\UpdateMyAccountSelfserve::class,

    // Transfer - User
    TransferCommand\User\CreateUser::class => CommandHandler\User\CreateUserFactory::class,
    TransferCommand\User\UpdateUser::class => CommandHandler\User\UpdateUser::class,
    TransferCommand\User\DeleteUser::class => CommandHandler\User\DeleteUserFactory::class,
    TransferCommand\User\RegisterUserSelfserve::class => CommandHandler\User\RegisterUserSelfserveFactory::class,
    TransferCommand\User\RemindUsernameSelfserve::class => CommandHandler\User\RemindUsernameSelfserve::class,
    TransferCommand\User\CreateUserSelfserve::class => CommandHandler\User\CreateUserSelfServeFactory::class,
    TransferCommand\User\UpdateUserSelfserve::class => CommandHandler\User\UpdateUserSelfserveFactory::class,
    TransferCommand\User\DeleteUserSelfserve::class => CommandHandler\User\DeleteUserSelfserveFactory::class,
    TransferCommand\User\CreatePartner::class => CommandHandler\User\CreatePartner::class,
    TransferCommand\User\UpdatePartner::class => CommandHandler\User\UpdatePartner::class,
    TransferCommand\User\DeletePartner::class => CommandHandler\User\DeletePartner::class,
    TransferCommand\User\UpdateUserLastLoginAt::class => CommandHandler\User\UpdateUserLastLoginAt::class,
    TransferCommand\User\RegisterConsultantAndOperator::class => CommandHandler\User\RegisterConsultantAndOperator::class,
    TransferCommand\User\AgreeTerms::class => CommandHandler\User\AgreeTerms::class,

    // Transfer - Team
    TransferCommand\Team\CreateTeam::class => CommandHandler\Team\CreateTeam::class,
    TransferCommand\Team\UpdateTeam::class => CommandHandler\Team\UpdateTeam::class,
    TransferCommand\Team\DeleteTeam::class => CommandHandler\Team\DeleteTeam::class,

    // Transfer - Printer
    TransferCommand\Printer\CreatePrinter::class => CommandHandler\Printer\CreatePrinter::class,
    TransferCommand\Printer\UpdatePrinter::class => CommandHandler\Printer\UpdatePrinter::class,
    TransferCommand\Printer\DeletePrinter::class => CommandHandler\Printer\DeletePrinter::class,

    // Transfer - TeamPrinter
    TransferCommand\TeamPrinter\CreateTeamPrinter::class => CommandHandler\TeamPrinter\CreateTeamPrinter::class,
    TransferCommand\TeamPrinter\UpdateTeamPrinter::class => CommandHandler\TeamPrinter\UpdateTeamPrinter::class,
    TransferCommand\TeamPrinter\DeleteTeamPrinter::class => CommandHandler\TeamPrinter\DeleteTeamPrinter::class,

    // Transfer - Cases
    TransferCommand\Cases\CreateCase::class => CommandHandler\Cases\CreateCase::class,
    TransferCommand\Cases\UpdateCase::class => CommandHandler\Cases\UpdateCase::class,
    TransferCommand\Cases\DeleteCase::class => CommandHandler\Cases\DeleteCase::class,
    TransferCommand\Cases\CloseCase::class => CommandHandler\Cases\CloseCase::class,
    TransferCommand\Cases\ReopenCase::class => CommandHandler\Cases\ReopenCase::class,

    //Transfer - Cases (note fields)
    TransferCommand\Cases\UpdateConvictionNote::class => CommandHandler\Cases\UpdateConvictionNote::class,
    TransferCommand\Cases\UpdateProhibitionNote::class => CommandHandler\Cases\UpdateProhibitionNote::class,
    TransferCommand\Cases\UpdatePenaltiesNote::class => CommandHandler\Cases\UpdatePenaltiesNote::class,

    // Transfer - Annual Test History
    TransferCommand\Cases\UpdateAnnualTestHistory::class => CommandHandler\Cases\UpdateAnnualTestHistory::class,

    // Transfer - Impounding
    TransferCommand\Cases\Impounding\CreateImpounding::class => CommandHandler\Cases\Impounding\CreateImpounding::class,
    TransferCommand\Cases\Impounding\UpdateImpounding::class => CommandHandler\Cases\Impounding\UpdateImpounding::class,
    TransferCommand\Cases\Impounding\DeleteImpounding::class => CommandHandler\Cases\Impounding\DeleteImpounding::class,

    // Transfer - ProposeToRevoke
    TransferCommand\Cases\ProposeToRevoke\CreateProposeToRevoke::class =>
        CommandHandler\Cases\ProposeToRevoke\CreateProposeToRevoke::class,
    TransferCommand\Cases\ProposeToRevoke\UpdateProposeToRevoke::class =>
        CommandHandler\Cases\ProposeToRevoke\UpdateProposeToRevoke::class,
    TransferCommand\Cases\ProposeToRevoke\UpdateProposeToRevokeSla::class =>
        CommandHandler\Cases\ProposeToRevoke\UpdateProposeToRevokeSla::class,

    // Transfer - Complaint
    TransferCommand\Complaint\CreateComplaint::class => CommandHandler\Complaint\CreateComplaint::class,
    TransferCommand\Complaint\UpdateComplaint::class => CommandHandler\Complaint\UpdateComplaint::class,
    TransferCommand\Complaint\DeleteComplaint::class => CommandHandler\Complaint\DeleteComplaint::class,

    // Transfer - Environmental Complaint
    TransferCommand\EnvironmentalComplaint\CreateEnvironmentalComplaint::class =>
        CommandHandler\EnvironmentalComplaint\CreateEnvironmentalComplaint::class,
    TransferCommand\EnvironmentalComplaint\UpdateEnvironmentalComplaint::class =>
        CommandHandler\EnvironmentalComplaint\UpdateEnvironmentalComplaint::class,
    TransferCommand\EnvironmentalComplaint\DeleteEnvironmentalComplaint::class =>
        CommandHandler\EnvironmentalComplaint\DeleteEnvironmentalComplaint::class,

    // Transfer - Submission
    TransferCommand\Submission\CreateSubmissionAction::class => CommandHandler\Submission\CreateSubmissionAction::class,
    TransferCommand\Submission\UpdateSubmissionAction::class => CommandHandler\Submission\UpdateSubmissionAction::class,

    TransferCommand\Submission\CreateSubmissionSectionComment::class =>
        CommandHandler\Submission\CreateSubmissionSectionComment::class,
    TransferCommand\Submission\UpdateSubmissionSectionComment::class =>
        CommandHandler\Submission\UpdateSubmissionSectionComment::class,
    TransferCommand\Submission\DeleteSubmissionSectionComment::class =>
        CommandHandler\Submission\DeleteSubmissionSectionComment::class,

    TransferCommand\Submission\CreateSubmission::class => CommandHandler\Submission\CreateSubmission::class,
    TransferCommand\Submission\UpdateSubmission::class => CommandHandler\Submission\UpdateSubmission::class,
    TransferCommand\Submission\DeleteSubmission::class => CommandHandler\Submission\DeleteSubmission::class,
    TransferCommand\Submission\CloseSubmission::class => CommandHandler\Submission\CloseSubmission::class,
    TransferCommand\Submission\ReopenSubmission::class => CommandHandler\Submission\ReopenSubmission::class,

    TransferCommand\Submission\FilterSubmissionSections::class =>
        CommandHandler\Submission\FilterSubmissionSections::class,
    TransferCommand\Submission\RefreshSubmissionSections::class =>
        CommandHandler\Submission\RefreshSubmissionSections::class,

    TransferCommand\Submission\AssignSubmission::class => CommandHandler\Submission\AssignSubmission::class,
    TransferCommand\Submission\InformationCompleteSubmission::class =>
        CommandHandler\Submission\InformationComplete::class,
    TransferCommand\Submission\StoreSubmissionSnapshot::class =>
        CommandHandler\Submission\StoreSubmissionSnapshot::class,

    // Transfer - Document
    TransferCommand\Document\CreateDocument::class => CommandHandler\Document\CreateDocument::class,
    Command\Document\CreateDocumentSpecific::class => CommandHandler\Document\CreateDocumentSpecific::class,
    TransferCommand\Document\DeleteDocument::class => CommandHandler\Document\DeleteDocument::class,
    TransferCommand\Document\DeleteDocuments::class => CommandHandler\Document\DeleteDocuments::class,
    TransferCommand\Document\CopyDocument::class => CommandHandler\Document\CopyDocument::class,
    TransferCommand\Document\MoveDocument::class => CommandHandler\Document\MoveDocument::class,
    TransferCommand\Document\UpdateDocumentLinks::class => CommandHandler\Document\UpdateDocumentLinks::class,
    TransferCommand\Document\PrintLetter::class => CommandHandler\Document\PrintLetter::class,
    TransferCommand\Document\PrintLetters::class => CommandHandler\Document\PrintLetters::class,
    Command\Document\RemoveDeletedDocuments::class => CommandHandler\Document\RemoveDeletedDocuments::class,

    // Transfer - DocumentTemplate
    TransferCommand\DocTemplate\Create::class => CommandHandler\DocTemplate\Create::class,
    TransferCommand\DocTemplate\Update::class => CommandHandler\DocTemplate\Update::class,
    TransferCommand\DocTemplate\Delete::class => CommandHandler\DocTemplate\Delete::class,

    // Transfer - CommunityLic
    TransferCommand\CommunityLic\Application\Create::class => CommandHandler\CommunityLic\Application\Create::class,
    TransferCommand\CommunityLic\Application\CreateOfficeCopy::class =>
        CommandHandler\CommunityLic\Application\CreateOfficeCopy::class,
    TransferCommand\CommunityLic\Licence\Create::class => CommandHandler\CommunityLic\Licence\Create::class,
    TransferCommand\CommunityLic\Licence\CreateOfficeCopy::class =>
        CommandHandler\CommunityLic\Licence\CreateOfficeCopy::class,
    TransferCommand\CommunityLic\Annul::class => CommandHandler\CommunityLic\Annul::class,
    TransferCommand\CommunityLic\Restore::class => CommandHandler\CommunityLic\Restore::class,
    TransferCommand\CommunityLic\Stop::class => CommandHandler\CommunityLic\Stop::class,
    TransferCommand\CommunityLic\Reprint::class => CommandHandler\CommunityLic\Reprint::class,
    TransferCommand\CommunityLic\EditSuspension::class => CommandHandler\CommunityLic\EditSuspension::class,

    // Conviction
    TransferCommand\Cases\Conviction\Create::class => CommandHandler\Cases\Conviction\Create::class,
    TransferCommand\Cases\Conviction\Update::class => CommandHandler\Cases\Conviction\Update::class,
    TransferCommand\Cases\Conviction\Delete::class => CommandHandler\Cases\Conviction\Delete::class,

    // Prohibition
    TransferCommand\Cases\Prohibition\Create::class => CommandHandler\Cases\Prohibition\Create::class,
    TransferCommand\Cases\Prohibition\Update::class => CommandHandler\Cases\Prohibition\Update::class,
    TransferCommand\Cases\Prohibition\Delete::class => CommandHandler\Cases\Prohibition\Delete::class,

    // Prohibition Defects
    TransferCommand\Cases\Prohibition\Defect\Create::class => CommandHandler\Cases\Prohibition\Defect\Create::class,
    TransferCommand\Cases\Prohibition\Defect\Update::class => CommandHandler\Cases\Prohibition\Defect\Update::class,
    TransferCommand\Cases\Prohibition\Defect\Delete::class => CommandHandler\Cases\Prohibition\Defect\Delete::class,

    // Transfer - Appeal
    TransferCommand\Cases\Hearing\CreateAppeal::class =>
        CommandHandler\Cases\Hearing\CreateAppeal::class,
    TransferCommand\Cases\Hearing\UpdateAppeal::class =>
        CommandHandler\Cases\Hearing\UpdateAppeal::class,

    // Transfer - Stay
    TransferCommand\Cases\Hearing\CreateStay::class =>
        CommandHandler\Cases\Hearing\CreateStay::class,
    TransferCommand\Cases\Hearing\UpdateStay::class =>
        CommandHandler\Cases\Hearing\UpdateStay::class,

    // Transfer - Licence Status Rule
    TransferCommand\LicenceStatusRule\CreateLicenceStatusRule::class
        => CommandHandler\LicenceStatusRule\CreateLicenceStatusRule::class,
    TransferCommand\LicenceStatusRule\UpdateLicenceStatusRule::class
        => CommandHandler\LicenceStatusRule\UpdateLicenceStatusRule::class,
    TransferCommand\LicenceStatusRule\DeleteLicenceStatusRule::class
        => CommandHandler\LicenceStatusRule\DeleteLicenceStatusRule::class,

    // Transfer - Document
    TransferCommand\Document\CreateLetter::class => CommandHandler\Document\CreateLetter::class,

    // Transfer - Licence Decisions
    TransferCommand\Licence\RevokeLicence::class => CommandHandler\Licence\Revoke::class,
    TransferCommand\Licence\CurtailLicence::class => CommandHandler\Licence\Curtail::class,
    TransferCommand\Licence\SuspendLicence::class => CommandHandler\Licence\Suspend::class,
    TransferCommand\Licence\SurrenderLicence::class => CommandHandler\Licence\Surrender::class,
    TransferCommand\Licence\ResetToValid::class => CommandHandler\Licence\ResetToValid::class,

    // Transfer - ConditionUndertaking
    TransferCommand\ConditionUndertaking\Create::class => CommandHandler\ConditionUndertaking\Create::class,
    TransferCommand\ConditionUndertaking\DeleteList::class => CommandHandler\ConditionUndertaking\DeleteList::class,
    TransferCommand\ConditionUndertaking\Delete::class => CommandHandler\ConditionUndertaking\Delete::class,
    TransferCommand\ConditionUndertaking\Update::class => CommandHandler\ConditionUndertaking\Update::class,

    // Transfer - Opposition
    TransferCommand\Opposition\CreateOpposition::class =>
        CommandHandler\Opposition\CreateOpposition::class,
    TransferCommand\Opposition\UpdateOpposition::class =>
        CommandHandler\Opposition\UpdateOpposition::class,
    TransferCommand\Opposition\DeleteOpposition::class =>
        CommandHandler\Opposition\DeleteOpposition::class,

    // Transfer - Statement
    TransferCommand\Cases\Statement\CreateStatement::class =>
        CommandHandler\Cases\Statement\CreateStatement::class,
    TransferCommand\Cases\Statement\UpdateStatement::class =>
        CommandHandler\Cases\Statement\UpdateStatement::class,
    TransferCommand\Cases\Statement\DeleteStatement::class =>
        CommandHandler\Cases\Statement\DeleteStatement::class,

    // Domain - Application
    Command\Application\CreateApplicationFee::class => CommandHandler\Application\CreateApplicationFee::class,
    Command\Application\ResetApplication::class => CommandHandler\Application\ResetApplication::class,
    Command\Application\GenerateLicenceNumber::class => CommandHandler\Application\GenerateLicenceNumber::class,
    Command\Application\UpdateApplicationCompletion::class
        => CommandHandler\Application\UpdateApplicationCompletion::class,
    Command\Application\UpdateVariationCompletion::class => CommandHandler\Application\UpdateVariationCompletion::class,
    Command\Application\CreateFee::class => CommandHandler\Application\CreateFee::class,
    Command\Application\CancelAllInterimFees::class => CommandHandler\Application\CancelAllInterimFees::class,
    Command\Application\UndoCancelAllInterimFees::class => CommandHandler\Application\UndoCancelAllInterimFees::class,
    Command\Application\CancelOutstandingFees::class => CommandHandler\Application\CancelOutstandingFees::class,
    Command\Application\SetDefaultTrafficAreaAndEnforcementArea::class
        => CommandHandler\Application\SetDefaultTrafficAreaAndEnforcementAreaFactory::class,
    Command\Application\DeleteApplication::class => CommandHandler\Application\DeleteApplication::class,

    // Domain - Application Operating Centre
    Command\ApplicationOperatingCentre\CreateApplicationOperatingCentre::class
        => CommandHandler\ApplicationOperatingCentre\CreateApplicationOperatingCentre::class,
    Command\ApplicationOperatingCentre\DeleteApplicationOperatingCentre::class
        => CommandHandler\ApplicationOperatingCentre\DeleteApplicationOperatingCentre::class,

    // Domain - Licence Operating Centre
    Command\LicenceOperatingCentre\AssociateS4::class
        => CommandHandler\LicenceOperatingCentre\AssociateS4::class,
    Command\LicenceOperatingCentre\DisassociateS4::class
        => CommandHandler\LicenceOperatingCentre\DisassociateS4::class,

    // Domain - Operating Centre
    Command\OperatingCentre\DeleteApplicationLinks::class =>
        CommandHandler\OperatingCentre\DeleteApplicationLinks::class,
    Command\OperatingCentre\DeleteConditionUndertakings::class =>
        CommandHandler\OperatingCentre\DeleteConditionUndertakings::class,

    // Domain - Condition Undertaking
    Command\Cases\ConditionUndertaking\CreateConditionUndertaking::class
        => CommandHandler\Cases\ConditionUndertaking\CreateConditionUndertaking::class,
    Command\Cases\ConditionUndertaking\DeleteConditionUndertakingS4::class
        => CommandHandler\Cases\ConditionUndertaking\DeleteConditionUndertakingS4::class,

    // Domain - Si, Erru
    Command\Cases\Si\SendResponse::class => CommandHandler\Cases\Si\SendResponseFactory::class,
    Command\Cases\Si\ComplianceEpisode::class => CommandHandler\Cases\Si\ComplianceEpisode::class,

    // Domain - Schedule41
    Command\Schedule41\CreateS4::class => CommandHandler\Schedule41\CreateS4::class,
    Command\Schedule41\ApproveS4::class => CommandHandler\Schedule41\ApproveS4::class,
    Command\Schedule41\ResetS4::class => CommandHandler\Schedule41\ResetS4::class,
    Command\Schedule41\RefuseS4::class => CommandHandler\Schedule41\RefuseS4::class,
    Command\Schedule41\CancelS4::class => CommandHandler\Schedule41\CancelS4::class,

    // Domain - Bus
    Command\Bus\CreateBusFee::class => CommandHandler\Bus\CreateBusFee::class,

    // Domain - Licence
    Command\Licence\CancelLicenceFees::class => CommandHandler\Licence\CancelLicenceFees::class,
    Command\Licence\UpdateTotalCommunityLicences::class => CommandHandler\Licence\UpdateTotalCommunityLicences::class,

    // Domain - common for Application, Licence, variation
    Command\Licence\SaveAddresses::class => CommandHandler\Licence\SaveAddresses::class,
    Command\Licence\SaveBusinessDetails::class => CommandHandler\Licence\SaveBusinessDetails::class,

    // Domain - Publications
    Command\Publication\PiHearing::class => CommandHandler\Publication\PiHearing::class,
    Command\Publication\PiDecision::class => CommandHandler\Publication\PiHearing::class,
    Command\Publication\CreateNextPublication::class => CommandHandler\Publication\CreateNextPublication::class,
    Command\Publication\Licence::class => CommandHandler\Publication\Licence::class,
    Command\Publication\Impounding::class => CommandHandler\Publication\Impounding::class,
    Command\Publication\CreatePoliceDocument::class => CommandHandler\Publication\CreatePoliceDocument::class,

    // Domain - Discs
    Command\Discs\CeaseGoodsDiscs::class => CommandHandler\Discs\CeaseGoodsDiscs::class,
    Command\Discs\CeaseGoodsDiscsForApplication::class => CommandHandler\Discs\CeaseGoodsDiscsForApplication::class,
    Command\Discs\CeasePsvDiscs::class => CommandHandler\Discs\CeasePsvDiscs::class,

    // Domain - Licence Vehicles
    Command\LicenceVehicle\RemoveLicenceVehicle::class => CommandHandler\LicenceVehicle\RemoveLicenceVehicle::class,
    TransferCommand\LicenceVehicle\UpdatePsvLicenceVehicle::class
        => CommandHandler\LicenceVehicle\UpdatePsvLicenceVehicle::class,
    Command\Vehicle\ProcessDuplicateVehicleWarning::class
        => CommandHandler\Vehicle\ProcessDuplicateVehicleWarning::class,
    Command\Vehicle\ProcessDuplicateVehicleWarnings::class
        => CommandHandler\Vehicle\ProcessDuplicateVehicleWarnings::class,
    Command\Vehicle\ProcessDuplicateVehicleRemoval::class
        => CommandHandler\Vehicle\ProcessDuplicateVehicleRemoval::class,
    Command\Vehicle\RemoveDuplicateVehicle::class
        => CommandHandler\Vehicle\RemoveDuplicateVehicle::class,

    // Domain - Transport Managers
    Command\Tm\DeleteTransportManagerLicence::class => CommandHandler\Tm\DeleteTransportManagerLicence::class,

    // Domain - ContactDetails
    Command\ContactDetails\SaveAddress::class => CommandHandler\ContactDetails\SaveAddress::class,

    // Domain - ContactDetails
    TransferCommand\ContactDetail\PhoneContact\Create::class =>
        CommandHandler\ContactDetails\PhoneContact\Create::class,
    TransferCommand\ContactDetail\PhoneContact\Update::class =>
        CommandHandler\ContactDetails\PhoneContact\Update::class,
    TransferCommand\ContactDetail\PhoneContact\Delete::class =>
        CommandHandler\ContactDetails\PhoneContact\Delete::class,

    // Domain - Task
    Command\Task\CreateTask::class => CommandHandler\Task\CreateTask::class,

    // Domain - Organisation
    Command\Organisation\UpdateTradingNames::class => CommandHandler\Organisation\UpdateTradingNames::class,
    TransferCommand\Organisation\CpidOrganisationExport::class
        => CommandHandler\Organisation\CpidOrganisationExport::class,

    // Domain - Fee
    Command\Fee\CreateFee::class => CommandHandler\Fee\CreateFee::class,
    Command\Fee\CancelFee::class => CommandHandler\Fee\CancelFee::class,
    Command\Fee\CancelIrfoGvPermitFees::class => CommandHandler\Fee\CancelIrfoGvPermitFees::class,
    Command\Fee\CancelIrfoPsvAuthFees::class => CommandHandler\Fee\CancelIrfoPsvAuthFees::class,
    Command\Fee\PayFee::class => CommandHandler\Fee\PayFee::class,
    Command\Fee\CreateOverpaymentFee::class => CommandHandler\Fee\CreateOverpaymentFee::class,
    Command\Fee\ResetFees::class => CommandHandler\Fee\ResetFees::class,
    TransferCommand\Fee\ApproveWaive::class => CommandHandler\Fee\ApproveWaive::class,
    TransferCommand\Fee\RecommendWaive::class => CommandHandler\Fee\RecommendWaive::class,
    TransferCommand\Fee\RejectWaive::class => CommandHandler\Fee\RejectWaive::class,
    TransferCommand\Fee\CreateFee::class => CommandHandler\Fee\CreateFee::class,
    TransferCommand\Fee\RefundFee::class => CommandHandler\Fee\RefundFee::class,
    Command\Fee\UpdateFeeStatus::class => CommandHandler\Fee\UpdateFeeStatus::class,

    // Domain - Transaction (formerly 'Payment')
    TransferCommand\Transaction\PayOutstandingFees::class => CommandHandler\Transaction\PayOutstandingFees::class,
    TransferCommand\Transaction\CompleteTransaction::class => CommandHandler\Transaction\CompleteTransaction::class,
    Command\Transaction\ResolvePayment::class => CommandHandler\Transaction\ResolvePayment::class,
    TransferCommand\Transaction\ReverseTransaction::class => CommandHandler\Transaction\ReverseTransaction::class,
    Command\Transaction\ResolveOutstandingPayments::class
        => CommandHandler\Transaction\ResolveOutstandingPayments::class,

    // Domain - ApplicationCompletion
    AppCompCommand\UpdateTypeOfLicenceStatus::class => AppCompCommandHandler\UpdateTypeOfLicenceStatus::class,
    AppCompCommand\UpdateAddressesStatus::class => AppCompCommandHandler\UpdateAddressesStatus::class,
    AppCompCommand\UpdateBusinessTypeStatus::class => AppCompCommandHandler\UpdateBusinessTypeStatus::class,
    AppCompCommand\UpdateConvictionsPenaltiesStatus::class
        => AppCompCommandHandler\UpdateConvictionsPenaltiesStatus::class,
    AppCompCommand\UpdateFinancialEvidenceStatus::class => AppCompCommandHandler\UpdateFinancialEvidenceStatus::class,
    AppCompCommand\UpdateFinancialHistoryStatus::class => AppCompCommandHandler\UpdateFinancialHistoryStatus::class,
    AppCompCommand\UpdateLicenceHistoryStatus::class => AppCompCommandHandler\UpdateLicenceHistoryStatus::class,
    AppCompCommand\UpdateOperatingCentresStatus::class => AppCompCommandHandler\UpdateOperatingCentresStatus::class,
    AppCompCommand\UpdatePeopleStatus::class => AppCompCommandHandler\UpdatePeopleStatus::class,
    AppCompCommand\UpdateSafetyStatus::class => AppCompCommandHandler\UpdateSafetyStatus::class,
    AppCompCommand\UpdateVehiclesStatus::class => AppCompCommandHandler\UpdateVehiclesStatus::class,
    AppCompCommand\UpdateUndertakingsStatus::class => AppCompCommandHandler\UpdateUndertakingsStatus::class,
    AppCompCommand\UpdateConditionsUndertakingsStatus::class
        => AppCompCommandHandler\UpdateConditionsUndertakingsStatus::class,
    AppCompCommand\UpdateVehiclesSizeStatus::class
    => AppCompCommandHandler\UpdateVehiclesSizeStatus::class,
    AppCompCommand\UpdatePsvDocumentaryEvidenceSmallStatus::class
    => AppCompCommandHandler\UpdatePsvDocumentaryEvidenceSmallStatus::class,
    AppCompCommand\UpdatePsvDocumentaryEvidenceLargeStatus::class
    => AppCompCommandHandler\UpdatePsvDocumentaryEvidenceLargeStatus::class,
    AppCompCommand\UpdatePsvMainOccupationUndertakingsStatus::class
    => AppCompCommandHandler\UpdatePsvMainOccupationUndertakingsStatus::class,
    AppCompCommand\UpdatePsvOperateLargeStatus::class
    => AppCompCommandHandler\UpdatePsvOperateLargeStatus::class,
    AppCompCommand\UpdatePsvOperateSmallStatus::class
    => AppCompCommandHandler\UpdatePsvOperateSmallStatus::class,
    AppCompCommand\UpdatePsvOperateNoveltyStatus::class
    => AppCompCommandHandler\UpdatePsvOperateNoveltyStatus::class,
    AppCompCommand\UpdatePsvSmallPartWrittenStatus::class
    => AppCompCommandHandler\UpdatePsvSmallPartWrittenStatus::class,
    AppCompCommand\UpdatePsvSmallConditionsStatus::class
    => AppCompCommandHandler\UpdatePsvSmallConditionsStatus::class,
    AppCompCommand\UpdateVehiclesPsvStatus::class => AppCompCommandHandler\UpdateVehiclesPsvStatus::class,
    AppCompCommand\UpdateTransportManagersStatus::class => AppCompCommandHandler\UpdateTransportManagersStatus::class,
    AppCompCommand\UpdateTaxiPhvStatus::class => AppCompCommandHandler\UpdateTaxiPhvStatus::class,
    AppCompCommand\UpdateCommunityLicencesStatus::class => AppCompCommandHandler\UpdateCommunityLicencesStatus::class,
    AppCompCommand\UpdateBusinessDetailsStatus::class => AppCompCommandHandler\UpdateBusinessDetailsStatus::class,
    AppCompCommand\UpdateDeclarationsInternalStatus::class =>
        AppCompCommandHandler\UpdateDeclarationsInternalStatus::class,

    // Domain - CommunityLic
    Command\CommunityLic\GenerateCoverLetter::class => CommandHandler\CommunityLic\GenerateCoverLetter::class,
    Command\CommunityLic\GenerateBatch::class => CommandHandler\CommunityLic\GenerateBatch::class,
    Command\CommunityLic\Application\CreateOfficeCopy::class =>
        CommandHandler\CommunityLic\Application\CreateOfficeCopy::class,
    Command\CommunityLic\Licence\CreateOfficeCopy::class => CommandHandler\CommunityLic\Licence\CreateOfficeCopy::class,
    Command\CommunityLic\BulkReprint::class => CommandHandler\CommunityLic\BulkReprint::class,
    Command\CommunityLic\ReportingBulkReprint::class => CommandHandler\CommunityLic\ReportingBulkReprint::class,
    Command\CommunityLic\ValidatingReprintCaller::class => CommandHandler\CommunityLic\ValidatingReprintCaller::class,

    // Cli - CommunityLic
    CommandCli\CommunityLic\Activate::class => CommandHandlerCli\CommunityLic\Activate::class,
    CommandCli\CommunityLic\Suspend::class => CommandHandlerCli\CommunityLic\Suspend::class,

    // Domain - Document
    Command\Document\CreateDocument::class => CommandHandler\Document\CreateDocument::class,
    Command\Document\GenerateAndStore::class => CommandHandler\Document\GenerateAndStore::class,
    Command\Document\GenerateAndStoreWithMultipleAddresses::class => CommandHandler\Document\GenerateAndStoreWithMultipleAddresses::class,
    Command\Document\UploadCsv::class => CommandHandler\Document\UploadCsv::class,
    TransferCommand\Document\GenerateAndStore::class => CommandHandler\Document\GenerateAndStore::class,
    TransferCommand\Document\Upload::class => CommandHandler\Document\Upload::class,

    // Domain - Report
    TransferCommand\Report\Upload::class => CommandHandler\Report\Upload::class,
    Command\BulkSend\ProcessEmail::class => CommandHandler\BulkSend\ProcessEmail::class,
    Command\Permits\PostScoringEmail::class => CommandHandler\Permits\PostScoringEmail::class,

    // Domain - Report Upload BulkSend

    Command\BulkSend\Email::class => CommandHandler\BulkSend\Email::class,
    Command\BulkSend\Letter::class => CommandHandler\BulkSend\Letter::class,

    // Domain - LicenceStatusRule
    Command\LicenceStatusRule\ProcessToRevokeCurtailSuspend::class
        => CommandHandler\LicenceStatusRule\ProcessToRevokeCurtailSuspend::class,
    Command\LicenceStatusRule\ProcessToValid::class
        => CommandHandler\LicenceStatusRule\ProcessToValid::class,
    Command\LicenceStatusRule\RemoveLicenceStatusRulesForLicence::class
        => CommandHandler\LicenceStatusRule\RemoveLicenceStatusRulesForLicence::class,

    // Transport Manager Application
    TransferCommand\TransportManagerApplication\Delete::class
        => CommandHandler\TransportManagerApplication\Delete::class,
    TransferCommand\TransportManagerApplication\Create::class
        => CommandHandler\TransportManagerApplication\Create::class,
    TransferCommand\TransportManagerApplication\UpdateStatus::class
        => CommandHandler\TransportManagerApplication\UpdateStatus::class,
    TransferCommand\TransportManagerApplication\UpdateDetails::class
        => CommandHandler\TransportManagerApplication\UpdateDetailsFactory::class,
    TransferCommand\TransportManagerApplication\CreateForResponsibilities::class =>
        CommandHandler\TransportManagerApplication\CreateForResponsibilities::class,
    TransferCommand\TransportManagerApplication\UpdateForResponsibilities::class =>
        CommandHandler\TransportManagerApplication\UpdateForResponsibilities::class,
    TransferCommand\TransportManagerApplication\DeleteForResponsibilities::class =>
        CommandHandler\TransportManagerApplication\DeleteForResponsibilities::class,
    TransferCommand\TransportManagerApplication\SendTmApplication::class =>
        CommandHandler\Email\SendTmApplication::class,
    TransferCommand\TransportManagerApplication\SendAmendTmApplication::class =>
        CommandHandler\Email\SendAmendTmApplication::class,
    TransferCommand\TransportManagerApplication\Submit::class =>
        CommandHandler\TransportManagerApplication\Submit::class,
    TransferCommand\TransportManagerApplication\OperatorSigned::class =>
        CommandHandler\TransportManagerApplication\OperatorSigned::class,

    // Email
    Command\Email\SendTmApplication::class => CommandHandler\Email\SendTmApplication::class,
    Command\Email\SendTmUserCreated::class => CommandHandler\Email\SendTmUserCreated::class,
    Command\Email\CreateCorrespondenceRecord::class => CommandHandler\Email\CreateCorrespondenceRecord::class,
    Command\Email\SendContinuationNotSought::class => CommandHandler\Email\SendContinuationNotSought::class,
    Command\Email\SendUsernameSingle::class => CommandHandler\Email\SendUsernameSingle::class,
    Command\Email\SendUsernameMultiple::class => CommandHandler\Email\SendUsernameMultiple::class,
    Command\Email\SendUserCreated::class => CommandHandler\Email\SendUserCreated::class,
    Command\Email\SendUserRegistered::class => CommandHandler\Email\SendUserRegistered::class,
    Command\Email\SendUserTemporaryPassword::class => CommandHandler\Email\SendUserTemporaryPassword::class,
    Command\Email\SendEbsrWithdrawn::class => CommandHandler\Email\SendEbsrWithdrawn::class,
    Command\Email\SendEbsrRefused::class => CommandHandler\Email\SendEbsrRefused::class,
    Command\Email\SendEbsrRefusedBySn::class => CommandHandler\Email\SendEbsrRefusedBySn::class,
    Command\Email\SendEbsrReceived::class => CommandHandler\Email\SendEbsrReceived::class,
    Command\Email\SendEbsrRefreshed::class => CommandHandler\Email\SendEbsrRefreshed::class,
    Command\Email\SendEbsrCancelled::class => CommandHandler\Email\SendEbsrCancelled::class,
    Command\Email\SendEbsrRegistered::class => CommandHandler\Email\SendEbsrRegistered::class,
    Command\Email\SendEbsrErrors::class => CommandHandler\Email\SendEbsrErrors::class,
    Command\Email\SendEbsrRequestMap::class => CommandHandler\Email\SendEbsrRequestMap::class,
    Command\Email\SendEcmtApggAppSubmitted::class => CommandHandler\Email\SendEcmtApggAppSubmitted::class,
    Command\Email\SendEcmtApggAppGranted::class => CommandHandler\Email\SendEcmtApggAppGranted::class,
    Command\Email\SendEcmtApsgAppSubmitted::class => CommandHandler\Email\SendEcmtApsgAppSubmitted::class,
    Command\Email\SendEcmtApsgIssued::class => CommandHandler\Email\SendEcmtApsgIssued::class,
    Command\Email\SendEcmtApggIssued::class => CommandHandler\Email\SendEcmtApggIssued::class,
    Command\Email\SendEcmtApsgUnsuccessful::class => CommandHandler\Email\SendEcmtApsgUnsuccessful::class,
    Command\Email\SendEcmtShortTermAutomaticallyWithdrawn::class => CommandHandler\Email\SendEcmtShortTermAutomaticallyWithdrawn::class,
    Command\Email\SendEcmtShortTermApggIssued::class => CommandHandler\Email\SendEcmtShortTermApggIssued::class,
    Command\Email\SendEcmtShortTermApsgIssued::class => CommandHandler\Email\SendEcmtShortTermApsgIssued::class,
    Command\Email\SendEcmtApsgPartSuccessful::class => CommandHandler\Email\SendEcmtApsgPartSuccessful::class,
    Command\Email\SendEcmtApsgSuccessful::class => CommandHandler\Email\SendEcmtApsgSuccessful::class,
    Command\Email\SendEcmtApsgPostScoring::class => CommandHandler\Email\SendEcmtApsgPostScoring::class,
    Command\Email\SendErruErrors::class => CommandHandler\Email\SendErruErrors::class,
    Command\Email\SendPublication::class => CommandHandler\Email\SendPublication::class,
    Command\Email\SendPsvOperatorListReport::class => CommandHandler\Email\SendPsvOperatorListReport::class,
    Command\Email\SendInternationalGoods::class => CommandHandler\Email\SendInternationalGoods::class,
    Command\Email\SendPtrNotificationForRegisteredUser::class => CommandHandler\Email\SendPtrNotificationForRegisteredUser::class,
    Command\Email\SendPtrNotificationForUnregisteredUser::class => CommandHandler\Email\SendPtrNotificationForUnregisteredUser::class,
    Command\Email\SendLiquidatedCompanyForUnregisteredUser::class => CommandHandler\Email\SendLiquidatedCompanyForUnregisteredUser::class,
    Command\Email\SendEcmtAutomaticallyWithdrawn::class => CommandHandler\Email\SendEcmtAutomaticallyWithdrawn::class,
    Command\Email\SendEcmtShortTermSuccessful::class => CommandHandler\Email\SendEcmtShortTermSuccessful::class,
    Command\Email\SendEcmtShortTermUnsuccessful::class => CommandHandler\Email\SendEcmtShortTermUnsuccessful::class,
    Command\Email\SendEcmtShortTermApsgPartSuccessful::class => CommandHandler\Email\SendEcmtShortTermApsgPartSuccessful::class,
    Command\Email\SendEcmtShortTermAppSubmitted::class => CommandHandler\Email\SendEcmtShortTermAppSubmitted::class,
    Command\Email\SendFailedOrganisationsList::class => CommandHandler\Email\SendFailedOrganisationsList::class,
    Command\Email\SendForgotPassword::class => CommandHandler\Email\SendForgotPassword::class,

    // Person
    Command\Person\Create::class => CommandHandler\Person\Create::class,
    Command\Person\UpdateFull::class => CommandHandler\Person\UpdateFull::class,

    // TM Employment
    TransferCommand\TmEmployment\DeleteList::class => CommandHandler\TmEmployment\DeleteList::class,
    TransferCommand\TmEmployment\Create::class => CommandHandler\TmEmployment\Create::class,
    TransferCommand\TmEmployment\Update::class => CommandHandler\TmEmployment\Update::class,
    Command\TransportManagerApplication\Snapshot::class => CommandHandler\TransportManagerApplication\Snapshot::class,

    // Transfer - Scan
    TransferCommand\Scan\CreateDocument::class => CommandHandler\Scan\CreateDocument::class,
    TransferCommand\Scan\CreateSeparatorSheet::class  => CommandHandler\Scan\CreateSeparatorSheet::class,
    TransferCommand\Scan\CreateContinuationSeparatorSheet::class  =>
        CommandHandler\Scan\CreateContinuationSeparatorSheet::class,

    // Domain - PrintScheduler
    Command\PrintScheduler\Enqueue::class  => CommandHandler\PrintScheduler\Enqueue::class,

    // Transfer - Operator
    TransferCommand\Operator\Create::class => CommandHandler\Operator\SaveOperator::class,
    TransferCommand\Operator\Update::class => CommandHandler\Operator\SaveOperator::class,
    TransferCommand\Operator\CreateUnlicensed::class => CommandHandler\Operator\CreateUnlicensed::class,
    TransferCommand\Operator\UpdateUnlicensed::class => CommandHandler\Operator\UpdateUnlicensed::class,
    TransferCommand\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle::class
        => CommandHandler\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle::class,
    TransferCommand\LicenceVehicle\DeleteUnlicensedOperatorLicenceVehicle::class
        => CommandHandler\LicenceVehicle\DeleteUnlicensedOperatorLicenceVehicle::class,
    TransferCommand\LicenceVehicle\CreateUnlicensedOperatorLicenceVehicle::class
        => CommandHandler\LicenceVehicle\CreateUnlicensedOperatorLicenceVehicle::class,

    // Message Queue
    CommandCli\MessageQueue\Enqueue::class => CommandHandlerCli\MessageQueue\Enqueue::class,
    CommandCli\MessageQueue\Consumer\TransXChange\TransXChangeConsumer::class => CommandHandlerCli\MessageQueue\Consumer\TransXChange\TransXChangeConsumer::class,
    CommandCli\MessageQueue\Consumer\CompaniesHouse\CompanyProfile::class => CommandHandlerCli\MessageQueue\Consumer\CompaniesHouse\CompanyProfile::class,
    CommandCli\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvency::class => CommandHandlerCli\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvency::class,
    CommandCli\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvencyDlq::class => CommandHandlerCli\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvencyDlq::class,
    CommandCli\MessageQueue\Consumer\CompaniesHouse\CompanyProfileDlq::class => CommandHandlerCli\MessageQueue\Consumer\CompaniesHouse\CompanyProfileDlq::class,

    // Vehicle
    Command\Vehicle\CreateGoodsVehicle::class => CommandHandler\Vehicle\CreateGoodsVehicle::class,
    TransferCommand\Vehicle\UpdateGoodsVehicle::class => CommandHandler\Vehicle\UpdateGoodsVehicle::class,
    TransferCommand\Vehicle\DeleteLicenceVehicle::class => CommandHandler\Vehicle\DeleteLicenceVehicle::class,
    Command\Vehicle\CeaseActiveDiscs::class => CommandHandler\Vehicle\CeaseActiveDiscs::class,
    TransferCommand\Vehicle\ReprintDisc::class => CommandHandler\Vehicle\ReprintDisc::class,
    Command\Vehicle\CreateGoodsDiscs::class => CommandHandler\Vehicle\CreateGoodsDiscs::class,
    TransferCommand\Vehicle\UpdateSection26::class => CommandHandler\Vehicle\UpdateSection26::class,

    // Transfer - InspectionRequest
    TransferCommand\InspectionRequest\Create::class => CommandHandler\InspectionRequest\Create::class,
    TransferCommand\InspectionRequest\Update::class => CommandHandler\InspectionRequest\Update::class,
    TransferCommand\InspectionRequest\Delete::class  => CommandHandler\InspectionRequest\Delete::class,
    TransferCommand\InspectionRequest\CreateFromGrant::class => CommandHandler\InspectionRequest\CreateFromGrant::class,

    // Domain - InspectionRequest
    Command\InspectionRequest\SendInspectionRequest::class =>
        CommandHandler\InspectionRequest\SendInspectionRequest::class,
    Command\InspectionRequest\CreateFromGrant::class  => CommandHandler\InspectionRequest\CreateFromGrant::class,

    // Transfer - ChangeOfEntity
    TransferCommand\ChangeOfEntity\CreateChangeOfEntity::class =>
        CommandHandler\ChangeOfEntity\CreateChangeOfEntity::class,
    TransferCommand\ChangeOfEntity\UpdateChangeOfEntity::class =>
        CommandHandler\ChangeOfEntity\UpdateChangeOfEntity::class,
    TransferCommand\ChangeOfEntity\DeleteChangeOfEntity::class =>
        CommandHandler\ChangeOfEntity\DeleteChangeOfEntity::class,

    // OrganisationPerson
    TransferCommand\OrganisationPerson\Create::class => CommandHandler\OrganisationPerson\Create::class,
    TransferCommand\OrganisationPerson\Update::class => CommandHandler\OrganisationPerson\Update::class,
    TransferCommand\OrganisationPerson\DeleteList::class => CommandHandler\OrganisationPerson\DeleteList::class,

    // Transfer - TransportManager
    TransferCommand\Tm\CheckRepute::class => CommandHandler\Tm\CheckReputeFactory::class,
    Command\Tm\CheckReputeProcessDocument::class => CommandHandler\Tm\CheckReputeProcessDocumentFactory::class,
    TransferCommand\Tm\Create::class => CommandHandler\Tm\Create::class,
    TransferCommand\Tm\CreateNewUser::class => CommandHandler\Tm\CreateNewUserFactory::class,
    TransferCommand\Tm\Update::class => CommandHandler\Tm\Update::class,
    TransferCommand\Tm\Remove::class => CommandHandler\Tm\Remove::class,
    TransferCommand\Tm\Merge::class => CommandHandler\Tm\Merge::class,
    TransferCommand\Tm\Unmerge::class => CommandHandler\Tm\Unmerge::class,
    TransferCommand\Tm\UndoDisqualification::class => CommandHandler\Tm\UndoDisqualification::class,
    Command\Tm\UpdateNysiisName::class => CommandHandler\Tm\UpdateNysiisName::class,

    // Task
    TransferCommand\Task\CloseTasks::class => CommandHandler\Task\CloseTasks::class,
    TransferCommand\Task\ReassignTasks::class => CommandHandler\Task\ReassignTasks::class,
    TransferCommand\Task\UpdateTask::class => CommandHandler\Task\UpdateTask::class,
    TransferCommand\Task\CreateTask::class => CommandHandler\Task\CreateTask::class,
    TransferCommand\Task\FlagUrgentTasks::class => CommandHandler\Task\FlagUrgentTasks::class,

    // PrivateHireLicence
    TransferCommand\PrivateHireLicence\DeleteList::class => CommandHandler\PrivateHireLicence\DeleteList::class,
    TransferCommand\PrivateHireLicence\Create::class => CommandHandler\PrivateHireLicence\CreateFactory::class,
    TransferCommand\PrivateHireLicence\Update::class => CommandHandler\PrivateHireLicence\UpdateFactory::class,

    // ContinuationDetail
    TransferCommand\ContinuationDetail\Submit::class => CommandHandler\ContinuationDetail\Submit::class,
    TransferCommand\ContinuationDetail\Update::class => CommandHandler\ContinuationDetail\Update::class,
    TransferCommand\ContinuationDetail\UpdateFinances::class => CommandHandler\ContinuationDetail\UpdateFinances::class,
    TransferCommand\ContinuationDetail\UpdateInsufficientFinances::class =>
        CommandHandler\ContinuationDetail\UpdateInsufficientFinances::class,
    TransferCommand\ContinuationDetail\Queue::class => CommandHandler\ContinuationDetail\Queue::class,
    TransferCommand\ContinuationDetail\PrepareContinuations::class =>
        CommandHandler\ContinuationDetail\PrepareContinuations::class,
    Command\ContinuationDetail\Process::class => CommandHandler\ContinuationDetail\Process::class,
    Command\ContinuationDetail\ProcessReminder::class => CommandHandler\ContinuationDetail\ProcessReminder::class,
    Command\ContinuationDetail\CreateSnapshot::class => CommandHandler\ContinuationDetail\CreateSnapshot::class,
    Command\ContinuationDetail\DigitalSendReminders::class =>
        CommandHandler\ContinuationDetail\DigitalSendReminders::class,
    Command\ContinuationDetail\GenerateCheckListReminder::class =>
        CommandHandler\ContinuationDetail\GenerateChecklistReminder::class,
    Command\ContinuationDetail\GenerateChecklistDocument::class =>
        CommandHandler\ContinuationDetail\GenerateChecklistDocument::class,

    // Continuation
    TransferCommand\Continuation\Create::class => CommandHandler\Continuation\Create::class,

    // Transport Manager Licence
    TransferCommand\TransportManagerLicence\Delete::class
    => CommandHandler\TransportManagerLicence\Delete::class,
    TransferCommand\TransportManagerLicence\UpdateForResponsibilities::class =>
        CommandHandler\TransportManagerLicence\UpdateForResponsibilities::class,
    TransferCommand\TransportManagerLicence\DeleteForResponsibilities::class =>
        CommandHandler\TransportManagerLicence\DeleteForResponsibilities::class,

    // CompaniesHouse
    Command\CompaniesHouse\InitialLoad::class => CommandHandler\CompaniesHouse\InitialLoad::class,
    Command\CompaniesHouse\Compare::class => CommandHandler\CompaniesHouse\Compare::class,
    Command\CompaniesHouse\CreateAlert::class => CommandHandler\CompaniesHouse\CreateAlert::class,
    TransferCommand\CompaniesHouse\CloseAlerts::class => CommandHandler\CompaniesHouse\CloseAlerts::class,

    // Domain - Queue
    QueueCommand\Complete::class => QueueCommandHandler\Complete::class,
    QueueCommand\Failed::class => QueueCommandHandler\Failed::class,
    QueueCommand\Retry::class => QueueCommandHandler\Retry::class,
    QueueCommand\Create::class => QueueCommandHandler\Create::class,
    QueueCommand\Delete::class => QueueCommandHandler\Delete::class,

    // Transfer - TmCaseDecision
    TransferCommand\TmCaseDecision\CreateReputeNotLost::class
        => CommandHandler\TmCaseDecision\CreateReputeNotLost::class,
    TransferCommand\TmCaseDecision\UpdateReputeNotLost::class
        => CommandHandler\TmCaseDecision\UpdateReputeNotLost::class,
    TransferCommand\TmCaseDecision\CreateNoFurtherAction::class
        => CommandHandler\TmCaseDecision\CreateNoFurtherAction::class,
    TransferCommand\TmCaseDecision\UpdateNoFurtherAction::class
        => CommandHandler\TmCaseDecision\UpdateNoFurtherAction::class,
    TransferCommand\TmCaseDecision\CreateDeclareUnfit::class
        => CommandHandler\TmCaseDecision\CreateDeclareUnfit::class,
    TransferCommand\TmCaseDecision\UpdateDeclareUnfit::class
        => CommandHandler\TmCaseDecision\UpdateDeclareUnfit::class,
    TransferCommand\TmCaseDecision\Delete::class => CommandHandler\TmCaseDecision\Delete::class,

    // Transfer - TmQualification
    TransferCommand\TmQualification\Create::class => CommandHandler\TmQualification\Create::class,
    TransferCommand\TmQualification\Update::class => CommandHandler\TmQualification\Update::class,
    TransferCommand\TmQualification\Delete::class  => CommandHandler\TmQualification\Delete::class,

    // Application Operating Centre
    TransferCommand\ApplicationOperatingCentre\Update::class => CommandHandler\ApplicationOperatingCentre\Update::class,

    // Variation Operating Centre
    TransferCommand\VariationOperatingCentre\Update::class => CommandHandler\VariationOperatingCentre\Update::class,

    // Licence Operating Centre
    TransferCommand\LicenceOperatingCentre\Update::class => CommandHandler\LicenceOperatingCentre\Update::class,

    /** @to-do Review whether these commands are still needed once front end controllers have been migrated */
    TransferCommand\Publication\Bus::class => CommandHandler\Publication\Bus::class,
    TransferCommand\Publication\Application::class => CommandHandler\Publication\Application::class,

    // Disqualification
    TransferCommand\Disqualification\Create::class => CommandHandler\Disqualification\Create::class,
    TransferCommand\Disqualification\Update::class => CommandHandler\Disqualification\Update::class,
    TransferCommand\Disqualification\Delete::class => CommandHandler\Disqualification\Delete::class,
    // Disc Printing
    TransferCommand\GoodsDisc\PrintDiscs::class => CommandHandler\GoodsDisc\PrintDiscs::class,
    TransferCommand\PsvDisc\PrintDiscs::class => CommandHandler\PsvDisc\PrintDiscs::class,
    TransferCommand\GoodsDisc\ConfirmPrinting::class => CommandHandler\GoodsDisc\ConfirmPrinting::class,
    TransferCommand\PsvDisc\ConfirmPrinting::class => CommandHandler\PsvDisc\ConfirmPrinting::class,
    Command\Discs\PrintDiscs::class => CommandHandler\Discs\PrintDiscs::class,
    Command\Discs\CreatePsvVehicleListForDiscs::class => CommandHandler\Discs\CreatePsvVehicleListForDiscs::class,

    // Admin - Financial Standing Rates
    TransferCommand\System\CreateFinancialStandingRate::class =>
        CommandHandler\System\CreateFinancialStandingRate::class,
    TransferCommand\System\UpdateFinancialStandingRate::class =>
        CommandHandler\System\UpdateFinancialStandingRate::class,
    TransferCommand\System\DeleteFinancialStandingRateList::class =>
        CommandHandler\System\DeleteFinancialStandingRateList::class,

    // Domain - Licence
    Command\Licence\ProcessContinuationNotSought::class => CommandHandler\Licence\ProcessContinuationNotSought::class,

    // Domain - Variation
    Command\Variation\EndInterim::class => CommandHandler\Variation\EndInterim::class,
    Command\Variation\ResetVariation::class => CommandHandler\Variation\ResetVariation::class,
    TransferCommand\Variation\GrantDirectorChange::class => CommandHandler\Variation\GrantDirectorChange::class,
    TransferCommand\Variation\DeleteVariation::class => CommandHandler\Variation\DeleteVariation::class,

    // Transfer - CPMS
    TransferCommand\Cpms\RequestReport::class => CommandHandler\Cpms\RequestReport::class,
    TransferCommand\Cpms\DownloadReport::class => CommandHandler\Cpms\DownloadReport::class,

    Command\SystemParameter\Update::class => CommandHandler\SystemParameter\Update::class,

    Command\PrintScheduler\PrintJob::class => CommandHandler\PrintScheduler\PrintJobFactory::class,

    // Transfer - SystemParameter
    TransferCommand\SystemParameter\CreateSystemParameter::class =>
        CommandHandler\SystemParameter\Create::class,
    TransferCommand\SystemParameter\UpdateSystemParameter::class =>
        CommandHandler\SystemParameter\Update::class,
    TransferCommand\SystemParameter\DeleteSystemParameter::class =>
        CommandHandler\SystemParameter\Delete::class,

    // Transfer - FeatureToggle
    TransferCommand\FeatureToggle\Create::class =>
        CommandHandler\FeatureToggle\Create::class,
    TransferCommand\FeatureToggle\Update::class =>
        CommandHandler\FeatureToggle\Update::class,
    TransferCommand\FeatureToggle\Delete::class                =>
        CommandHandler\FeatureToggle\Delete::class,

    // Transfer - Messaging
    TransferCommand\Messaging\Conversation\Close::class        =>
        CommandHandler\Messaging\Conversation\Close::class,
    TransferCommand\Messaging\Conversation\Disable::class      =>
        CommandHandler\Messaging\Conversation\Disable::class,
    TransferCommand\Messaging\Conversation\Enable::class       =>
        CommandHandler\Messaging\Conversation\Enable::class,
    TransferCommand\Messaging\DisableFileUpload::class =>
        CommandHandler\Messaging\DisableFileUpload::class,
    TransferCommand\Messaging\EnableFileUpload::class =>
        CommandHandler\Messaging\EnableFileUpload::class,

    Command\Email\SendNewMessageNotificationToOperators::class => CommandHandler\Email\SendNewMessageNotificationToOperators::class,

    // Transfer - IRHP Permit
    TransferCommand\IrhpPermit\Replace::class                  =>
        CommandHandler\IrhpPermit\Replace::class,
    TransferCommand\IrhpPermit\Terminate::class                =>
        CommandHandler\IrhpPermit\Terminate::class,

    // Transfer - IRHP Permit Application
    TransferCommand\IrhpApplication\Create::class              =>
        CommandHandler\IrhpApplication\Create::class,

    // Transfer - IRHP Permit Stock
    TransferCommand\IrhpPermitStock\Create::class =>
        CommandHandler\IrhpPermitStock\Create::class,
    TransferCommand\IrhpPermitStock\Update::class =>
        CommandHandler\IrhpPermitStock\Update::class,
    TransferCommand\IrhpPermitStock\Delete::class =>
        CommandHandler\IrhpPermitStock\Delete::class,

    // Transfer - IRHP Permit Window
    TransferCommand\IrhpPermitWindow\Create::class =>
        CommandHandler\IrhpPermitWindow\Create::class,
    TransferCommand\IrhpPermitWindow\Update::class =>
        CommandHandler\IrhpPermitWindow\Update::class,
    TransferCommand\IrhpPermitWindow\Delete::class =>
        CommandHandler\IrhpPermitWindow\Delete::class,
    Command\IrhpPermitWindow\Close::class =>
        CommandHandler\IrhpPermitWindow\Close::class,

    // Transfer - IRHP Permit Range
    TransferCommand\IrhpPermitRange\Create::class =>
        CommandHandler\IrhpPermitRange\Create::class,
    TransferCommand\IrhpPermitRange\Update::class =>
        CommandHandler\IrhpPermitRange\Update::class,
    TransferCommand\IrhpPermitRange\Delete::class =>
        CommandHandler\IrhpPermitRange\Delete::class,

    // Transfer - IRHP Permit Sector
    TransferCommand\IrhpPermitSector\Update::class =>
        CommandHandler\IrhpPermitSector\Update::class,
    // Backend command - IRHP Permit Sector
    Command\IrhpPermitSector\Create::class => CommandHandler\IrhpPermitSector\Create::class,

    // Transfer - IRHP Permit Jurisdiction
    TransferCommand\IrhpPermitJurisdiction\Update::class =>
        CommandHandler\IrhpPermitJurisdiction\Update::class,
    // Backend command - IRHP Permit Jurisdiction
    Command\IrhpPermitJurisdiction\Create::class => CommandHandler\IrhpPermitJurisdiction\Create::class,

    // Sla Target Dates
    TransferCommand\System\CreateSlaTargetDate::class => CommandHandler\System\CreateSlaTargetDate::class,
    TransferCommand\System\UpdateSlaTargetDate::class => CommandHandler\System\UpdateSlaTargetDate::class,
    Command\System\GenerateSlaTargetDate::class => CommandHandler\System\GenerateSlaTargetDate::class,

    // Task Allocation
    TransferCommand\TaskAllocationRule\DeleteList::class => CommandHandler\TaskAllocationRule\DeleteList::class,
    TransferCommand\TaskAllocationRule\Create::class => CommandHandler\TaskAllocationRule\Create::class,
    TransferCommand\TaskAllocationRule\Update::class => CommandHandler\TaskAllocationRule\Update::class,

    // Task Alpha Split
    TransferCommand\TaskAlphaSplit\DeleteList::class => CommandHandler\TaskAlphaSplit\DeleteList::class,
    TransferCommand\TaskAlphaSplit\Delete::class => CommandHandler\TaskAlphaSplit\Delete::class,
    TransferCommand\TaskAlphaSplit\Create::class => CommandHandler\TaskAlphaSplit\Create::class,
    TransferCommand\TaskAlphaSplit\Update::class => CommandHandler\TaskAlphaSplit\Update::class,

    // Admin :: System Messages
    TransferCommand\System\InfoMessage\Create::class => CommandHandler\System\InfoMessage\Create::class,
    TransferCommand\System\InfoMessage\Update::class => CommandHandler\System\InfoMessage\Update::class,
    TransferCommand\System\InfoMessage\Delete::class => CommandHandler\System\InfoMessage\Delete::class,

    // Admin :: Public holidays
    TransferCommand\System\PublicHoliday\Create::class => CommandHandler\System\PublicHoliday\Create::class,
    TransferCommand\System\PublicHoliday\Update::class => CommandHandler\System\PublicHoliday\Update::class,
    TransferCommand\System\PublicHoliday\Delete::class => CommandHandler\System\PublicHoliday\Delete::class,

    // Command - CommunityLc
    Command\Licence\EnqueueContinuationNotSought::class =>
        CommandHandler\Licence\EnqueueContinuationNotSought::class,
    Command\Licence\CreateSurrenderPsvLicenceTasks::class =>
        CommandHandler\Licence\CreateSurrenderPsvLicenceTasks::class,

    // Command - ConditionUndertaking
    Command\ConditionUndertaking\CreateSmallVehicleCondition::class =>
        CommandHandler\ConditionUndertaking\CreateSmallVehicleCondition::class,
    Command\ConditionUndertaking\CreateLightGoodsVehicleCondition::class =>
        CommandHandler\ConditionUndertaking\CreateLightGoodsVehicleCondition::class,

    // GovUkAccount
    TransferCommand\GovUkAccount\GetGovUkAccountRedirect::class =>
        CommandHandler\GovUkAccount\GetGovUkAccountRedirectFactory::class,
    TransferCommand\GovUkAccount\ProcessAuthResponse::class =>
        CommandHandler\GovUkAccount\ProcessAuthResponseFactory::class,

    // Digital Signatures
    Command\DigitalSignature\UpdateApplication::class => CommandHandler\DigitalSignature\UpdateApplication::class,
    Command\DigitalSignature\UpdateContinuationDetail::class => CommandHandler\DigitalSignature\UpdateContinuationDetail::class,
    Command\DigitalSignature\UpdateSurrender::class => CommandHandler\DigitalSignature\UpdateSurrenderFactory::class,
    Command\DigitalSignature\UpdateTmApplication::class => CommandHandler\DigitalSignature\UpdateTmApplication::class,

    // DataRetention
    Command\DataRetention\Populate::class => CommandHandler\DataRetention\Populate::class,
    Command\DataRetention\DeleteEntities::class => CommandHandler\DataRetention\DeleteEntities::class,
    Command\DataRetention\Precheck::class => CommandHandler\DataRetention\Precheck::class,

    TransferCommand\DataRetention\MarkForDelete::class => CommandHandler\DataRetention\UpdateActionConfirmation::class,
    TransferCommand\DataRetention\MarkForReview::class => CommandHandler\DataRetention\UpdateActionConfirmation::class,
    TransferCommand\DataRetention\DelayItems::class => CommandHandler\DataRetention\DelayItems::class,
    TransferCommand\DataRetention\AssignItems::class => CommandHandler\DataRetention\AssignItems::class,
    TransferCommand\DataRetention\UpdateRule::class => CommandHandler\DataRetention\UpdateRule::class,

    // Surrender
    TransferCommand\Surrender\Create::class => CommandHandler\Surrender\Create::class,
    TransferCommand\Surrender\Update::class => CommandHandler\Surrender\Update::class,
    TransferCommand\Surrender\Delete::class => CommandHandler\Surrender\Delete::class,
    TransferCommand\Surrender\SubmitForm::class => CommandHandler\Surrender\SubmitForm::class,
    Command\Surrender\Snapshot::class => CommandHandler\Surrender\Snapshot::class,
    TransferCommand\Surrender\Approve::class => CommandHandler\Surrender\Approve::class,
    TransferCommand\Surrender\Withdraw::class => CommandHandler\Surrender\Withdraw::class,
    Command\Surrender\Clear::class => CommandHandler\Surrender\Clear::class,

    // Permits - IRHP application
    TransferCommand\IrhpApplication\UpdateCountries::class => CommandHandler\IrhpApplication\UpdateCountries::class,
    TransferCommand\IrhpApplication\UpdateMultipleNoOfPermits::class => CommandHandler\IrhpApplication\UpdateMultipleNoOfPermits::class,
    TransferCommand\IrhpApplication\UpdateDeclaration::class => CommandHandler\IrhpApplication\UpdateDeclaration::class,
    TransferCommand\IrhpApplication\SubmitApplication::class => CommandHandler\IrhpApplication\SubmitApplication::class,
    TransferCommand\IrhpApplication\CancelApplication::class => CommandHandler\IrhpApplication\Cancel::class,
    TransferCommand\IrhpApplication\Terminate::class => CommandHandler\IrhpApplication\Terminate::class,
    TransferCommand\IrhpApplication\Withdraw::class => CommandHandler\IrhpApplication\Withdraw::class,
    TransferCommand\IrhpApplication\ResetToNotYetSubmittedFromValid::class => CommandHandler\IrhpApplication\ResetToNotYetSubmittedFromValid::class,
    TransferCommand\IrhpApplication\ResetToNotYetSubmittedFromCancelled::class => CommandHandler\IrhpApplication\ResetToNotYetSubmittedFromCancelled::class,
    TransferCommand\IrhpApplication\ReviveFromWithdrawn::class => CommandHandler\IrhpApplication\ReviveFromWithdrawn::class,
    TransferCommand\IrhpApplication\ReviveFromUnsuccessful::class => CommandHandler\IrhpApplication\ReviveFromUnsuccessful::class,
    TransferCommand\IrhpApplication\Grant::class => CommandHandler\IrhpApplication\Grant::class,
    TransferCommand\IrhpApplication\SubmitApplicationStep::class => CommandHandler\IrhpApplication\SubmitApplicationStep::class,
    TransferCommand\IrhpApplication\SubmitApplicationPath::class => CommandHandler\IrhpApplication\SubmitApplicationPath::class,
    Command\IrhpApplication\RegenerateApplicationFee::class => CommandHandler\IrhpApplication\RegenerateApplicationFee::class,
    Command\IrhpApplication\RegenerateIssueFee::class => CommandHandler\IrhpApplication\RegenerateIssueFee::class,
    TransferCommand\IrhpApplication\CreateFull::class => CommandHandler\IrhpApplication\CreateFull::class,
    TransferCommand\IrhpApplication\UpdateFull::class => CommandHandler\IrhpApplication\UpdateFull::class,
    Command\IrhpApplication\CreateDefaultIrhpPermitApplications::class => CommandHandler\IrhpApplication\CreateDefaultIrhpPermitApplications::class,
    Command\IrhpApplication\Expire::class => CommandHandler\IrhpApplication\Expire::class,
    TransferCommand\IrhpApplication\UpdatePeriod::class => CommandHandler\IrhpApplication\UpdatePeriod::class,
    TransferCommand\IrhpApplication\UpdateCandidatePermitSelection::class => CommandHandler\IrhpApplication\UpdateCandidatePermitSelection::class,

    // Irhp Permit Application
    Command\IrhpPermitApplication\CreateForIrhpApplication::class => CommandHandler\IrhpPermitApplication\CreateForIrhpApplication::class,
    Command\IrhpPermitApplication\UpdateIrhpPermitWindow::class => CommandHandler\IrhpPermitApplication\UpdateIrhpPermitWindow::class,

    // Permits Decline
    TransferCommand\Permits\AcceptIrhpPermits::class => CommandHandler\Permits\AcceptIrhpPermits::class,

    // Permits - internal backend
    Command\Permits\AllocateCandidatePermits::class => CommandHandler\Permits\AllocateCandidatePermits::class,
    Command\Permits\AllocateIrhpApplicationPermits::class => CommandHandler\Permits\AllocateIrhpApplicationPermits::class,
    Command\Permits\GeneratePermitDocuments::class => CommandHandler\Permits\GeneratePermitDocuments::class,
    TransferCommand\Permits\PrintPermits::class => CommandHandler\Permits\PrintPermits::class,
    Command\Permits\RunScoring::class => CommandHandler\Permits\RunScoring::class,
    Command\Permits\AcceptScoring::class => CommandHandler\Permits\AcceptScoring::class,
    Command\Permits\ProceedToStatus::class => CommandHandler\Permits\ProceedToStatus::class,
    Command\Permits\PostSubmitTasks::class => CommandHandler\Permits\PostSubmitTasks::class,
    Command\Permits\GenerateReport::class => CommandHandler\Permits\GenerateReport::class,
    TransferCommand\Permits\QueueReport::class => CommandHandler\Permits\QueueReport::class,
    Command\Permits\RoadworthinessReport::class => CommandHandler\Permits\RoadworthinessReport::class,

    // Create HTML Snapshot
    Command\IrhpApplication\StoreSnapshot::class =>
        CommandHandler\IrhpApplication\StoreSnapshot::class,

    // IrhpPermit Internal Backend Command
    Command\IrhpPermit\ReplacementIrhpPermit::class => CommandHandler\IrhpPermit\CreateReplacement::class,
    Command\IrhpPermit\GenerateCoverLetterDocument::class
        => CommandHandler\IrhpPermit\GenerateCoverLetterDocument::class,
    Command\IrhpPermit\GeneratePermitDocument::class => CommandHandler\IrhpPermit\GeneratePermitDocument::class,

    TransferCommand\IrhpCandidatePermit\Delete::class => CommandHandler\IrhpCandidatePermit\Delete::class,
    TransferCommand\IrhpCandidatePermit\Update::class => CommandHandler\IrhpCandidatePermit\Update::class,
    TransferCommand\IrhpCandidatePermit\Create::class => CommandHandler\IrhpCandidatePermit\Create::class,

    // IrhpApplication update
    TransferCommand\IrhpApplication\UpdateCheckAnswers::class => CommandHandler\IrhpApplication\UpdateCheckAnswers::class,

    // Permits - run/accept
    TransferCommand\Permits\QueueRunScoring::class => CommandHandler\Permits\QueueRunScoring::class,
    TransferCommand\Permits\QueueAcceptScoring::class => CommandHandler\Permits\QueueAcceptScoring::class,

    // Cli - Permits
    CommandCli\Permits\MarkSuccessfulDaPermitApplications::class =>
        CommandHandlerCli\Permits\MarkSuccessfulDaPermitApplications::class,
    CommandCli\Permits\MarkSuccessfulRemainingPermitApplications::class =>
        CommandHandlerCli\Permits\MarkSuccessfulRemainingPermitApplications::class,
    CommandCli\Permits\MarkSuccessfulSectorPermitApplications::class =>
        CommandHandlerCli\Permits\MarkSuccessfulSectorPermitApplications::class,
    CommandCli\Permits\InitialiseScope::class =>
        CommandHandlerCli\Permits\InitialiseScope::class,
    CommandCli\Permits\ApplyRangesToSuccessfulPermitApplications::class =>
        CommandHandlerCli\Permits\ApplyRangesToSuccessfulPermitApplications::class,
    CommandCli\Permits\UploadScoringResult::class =>
        CommandHandlerCli\Permits\UploadScoringResult::class,
    CommandCli\Permits\UploadScoringLog::class =>
        CommandHandlerCli\Permits\UploadScoringLog::class,
    CommandCli\Permits\GeneratePermits::class =>
        CommandHandlerCli\Permits\GeneratePermits::class,

    // Templates
    TransferCommand\Template\UpdateTemplateSource::class => CommandHandler\Template\UpdateTemplateSource::class,

    //FeeType
    TransferCommand\FeeType\Update::class => CommandHandler\FeeType\Update::class,

    //TranslationKey
    TransferCommand\TranslationKey\Update::class => CommandHandler\TranslationKey\Update::class,
    TransferCommand\TranslationKey\Create::class => CommandHandler\TranslationKey\Create::class,
    TransferCommand\TranslationKey\Delete::class => CommandHandler\TranslationKey\Delete::class,
    TransferCommand\TranslationKey\GenerateCache::class => CommandHandler\TranslationKey\GenerateCache::class,
    Command\TranslationKeyText\Create::class => CommandHandler\TranslationKeyText\Create::class,
    Command\TranslationKeyText\Update::class => CommandHandler\TranslationKeyText\Update::class,
    TransferCommand\TranslationKeyText\Delete::class => CommandHandler\TranslationKeyText\Delete::class,

    //Replacement
    TransferCommand\Replacement\Update::class => CommandHandler\Replacement\Update::class,
    TransferCommand\Replacement\Create::class => CommandHandler\Replacement\Create::class,

    //Caches
    Command\Cache\Generate::class => CommandHandler\Cache\Generate::class,
    Command\Cache\ClearForLicence::class => CommandHandler\Cache\ClearForLicence::class,
    Command\Cache\ClearForOrganisation::class => CommandHandler\Cache\ClearForOrganisation::class,

    // DvsaReports
    TransferCommand\DvsaReports\GetRedirect::class => CommandHandler\DvsaReports\GetRedirectFactory::class,

    CommandCli\InterimEndDateEnforcement::class => CommandHandlerCli\InterimEndDateEnforcement::class,

    Command\Messaging\Conversation\StoreSnapshot::class  => CommandHandler\Messaging\Conversation\StoreSnapshot::class,
    Command\Messaging\Conversation\StoreEnhancedSnapshot::class  => CommandHandler\Messaging\Conversation\StoreEnhancedSnapshot::class,
    TransferCommand\Messaging\Message\Create::class      => CommandHandler\Messaging\Message\Create::class,
    TransferCommand\Messaging\Conversation\Create::class => CommandHandler\Messaging\Conversation\Create::class,

    // Local Authority
    TransferCommand\LocalAuthority\Update::class => CommandHandler\LocalAuthority\Update::class,

    Command\Email\SendBSRNotificationToLTAs::class => CommandHandler\Email\SendBSRNotificationToLTAs::class,

    // Letter
    TransferCommand\Letter\LetterType\Create::class => CommandHandler\Letter\LetterType\Create::class,
    TransferCommand\Letter\LetterType\Update::class => CommandHandler\Letter\LetterType\Update::class,
    TransferCommand\Letter\LetterType\Delete::class => CommandHandler\Letter\LetterType\Delete::class,
    TransferCommand\Letter\LetterSection\Create::class => CommandHandler\Letter\LetterSection\Create::class,
    TransferCommand\Letter\LetterSection\Update::class => CommandHandler\Letter\LetterSection\Update::class,
    TransferCommand\Letter\LetterSection\Delete::class => CommandHandler\Letter\LetterSection\Delete::class,
    TransferCommand\Letter\LetterIssue\Create::class => CommandHandler\Letter\LetterIssue\Create::class,
    TransferCommand\Letter\LetterIssue\Update::class => CommandHandler\Letter\LetterIssue\Update::class,
    TransferCommand\Letter\LetterIssue\Delete::class => CommandHandler\Letter\LetterIssue\Delete::class,
    TransferCommand\Letter\LetterIssueType\Create::class => CommandHandler\Letter\LetterIssueType\Create::class,
    TransferCommand\Letter\LetterIssueType\Update::class => CommandHandler\Letter\LetterIssueType\Update::class,
    TransferCommand\Letter\LetterIssueType\Delete::class => CommandHandler\Letter\LetterIssueType\Delete::class,
    TransferCommand\Letter\LetterTodo\Create::class => CommandHandler\Letter\LetterTodo\Create::class,
    TransferCommand\Letter\LetterTodo\Update::class => CommandHandler\Letter\LetterTodo\Update::class,
    TransferCommand\Letter\LetterTodo\Delete::class => CommandHandler\Letter\LetterTodo\Delete::class,
    TransferCommand\Letter\LetterAppendix\Create::class => CommandHandler\Letter\LetterAppendix\Create::class,
    TransferCommand\Letter\LetterAppendix\Update::class => CommandHandler\Letter\LetterAppendix\Update::class,
    TransferCommand\Letter\LetterAppendix\Delete::class => CommandHandler\Letter\LetterAppendix\Delete::class,
    TransferCommand\Letter\LetterInstance\Create::class => CommandHandler\Letter\LetterInstance\Create::class,
    TransferCommand\Letter\LetterInstance\Update::class => CommandHandler\Letter\LetterInstance\Update::class,
    TransferCommand\Letter\LetterInstance\Generate::class => CommandHandler\Letter\LetterInstance\Generate::class,
    TransferCommand\Letter\LetterInstance\PrepareToSend::class => CommandHandler\Letter\LetterInstance\PrepareToSend::class,
    TransferCommand\Letter\LetterInstanceIssue\UpdateContent::class => CommandHandler\Letter\LetterInstanceIssue\UpdateContent::class,
    TransferCommand\Letter\LetterInstanceAppendix\UpdateContent::class => CommandHandler\Letter\LetterInstanceAppendix\UpdateContent::class,
    TransferCommand\Letter\MasterTemplate\Create::class => CommandHandler\Letter\MasterTemplate\Create::class,
    TransferCommand\Letter\MasterTemplate\Update::class => CommandHandler\Letter\MasterTemplate\Update::class,
    TransferCommand\Letter\MasterTemplate\Delete::class => CommandHandler\Letter\MasterTemplate\Delete::class,
    TransferCommand\Letter\LetterTestData\Create::class => CommandHandler\Letter\LetterTestData\Create::class,
    TransferCommand\Letter\LetterTestData\Update::class => CommandHandler\Letter\LetterTestData\Update::class,
    TransferCommand\Letter\LetterTestData\Delete::class => CommandHandler\Letter\LetterTestData\Delete::class,
];
