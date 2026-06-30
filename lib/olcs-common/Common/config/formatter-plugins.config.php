<?php

use Common\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm;
use Common\Service\Section\VehicleSafety\Vehicle\Formatter\VrmFactory;
use Common\Service\Table\Formatter\AccessedCorrespondence;
use Common\Service\Table\Formatter\AccessedCorrespondenceFactory;
use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\AddressLines;
use Common\Service\Table\Formatter\BusRegNumberLink;
use Common\Service\Table\Formatter\BusRegStatus;
use Common\Service\Table\Formatter\CaseEntityName;
use Common\Service\Table\Formatter\CaseEntityNrStatus;
use Common\Service\Table\Formatter\CaseLink;
use Common\Service\Table\Formatter\CaseTrafficArea;
use Common\Service\Table\Formatter\CommunityLicenceIssueNo;
use Common\Service\Table\Formatter\CommunityLicenceStatus;
use Common\Service\Table\Formatter\ConditionsUndertakingsType;
use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\ConvictionDescription;
use Common\Service\Table\Formatter\DashboardApplicationLink;
use Common\Service\Table\Formatter\DashboardTmActionLink;
use Common\Service\Table\Formatter\DataRetentionAssignedTo;
use Common\Service\Table\Formatter\DataRetentionRecordLink;
use Common\Service\Table\Formatter\DataRetentionRuleActionType;
use Common\Service\Table\Formatter\DataRetentionRuleAdminLink;
use Common\Service\Table\Formatter\DataRetentionRuleIsEnabled;
use Common\Service\Table\Formatter\DataRetentionRuleLink;
use Common\Service\Table\Formatter\DashboardTmApplicationStatus;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DateTime;
use Common\Service\Table\Formatter\DisqualifyUrl;
use Common\Service\Table\Formatter\DisqualifyUrlFactory;
use Common\Service\Table\Formatter\DocumentDescription;
use Common\Service\Table\Formatter\DocumentSubcategory;
use Common\Service\Table\Formatter\EbsrDocumentLink;
use Common\Service\Table\Formatter\EbsrDocumentStatus;
use Common\Service\Table\Formatter\EbsrRegNumberLink;
use Common\Service\Table\Formatter\EbsrVariationNumber;
use Common\Service\Table\Formatter\EventHistoryDescription;
use Common\Service\Table\Formatter\EventHistoryUser;
use Common\Service\Table\Formatter\ExternalConversationLink;
use Common\Service\Table\Formatter\ExternalConversationLinkFactory;
use Common\Service\Table\Formatter\ExternalConversationStatus;
use Common\Service\Table\Formatter\ExternalConversationStatusFactory;
use Common\Service\Table\Formatter\FeatureToggleEditLink;
use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeAmountSum;
use Common\Service\Table\Formatter\FeeIdUrl;
use Common\Service\Table\Formatter\FeeNoAndStatus;
use Common\Service\Table\Formatter\FeeNoAndStatusFactory;
use Common\Service\Table\Formatter\FeeStatus;
use Common\Service\Table\Formatter\FeeTransactionDate;
use Common\Service\Table\Formatter\FeeTransactionDateFactory;
use Common\Service\Table\Formatter\FeeUrl;
use Common\Service\Table\Formatter\FeeUrlExternal;
use Common\Service\Table\Formatter\FileExtension;
use Common\Service\Table\Formatter\HideIfClosedRadio;
use Common\Service\Table\Formatter\InspectionRequestId;
use Common\Service\Table\Formatter\InterimOcCheckbox;
use Common\Service\Table\Formatter\InterimVehiclesCheckbox;
use Common\Service\Table\Formatter\InternalConversationLink;
use Common\Service\Table\Formatter\InternalConversationLinkFactory;
use Common\Service\Table\Formatter\InternalLicenceNumberLink;
use Common\Service\Table\Formatter\InternalLicencePermitReference;
use Common\Service\Table\Formatter\IrhpPermitApplicationRefLink;
use Common\Service\Table\Formatter\IrhpPermitJurisdictionPermitNumber;
use Common\Service\Table\Formatter\IrhpPermitJurisdictionTrafficArea;
use Common\Service\Table\Formatter\IrhpPermitNumberInternal;
use Common\Service\Table\Formatter\IrhpPermitOrganisationName;
use Common\Service\Table\Formatter\IrhpPermitRangePermitNumber;
use Common\Service\Table\Formatter\IrhpPermitRangeReplacement;
use Common\Service\Table\Formatter\IrhpPermitRangeReserve;
use Common\Service\Table\Formatter\IrhpPermitRangeRestrictedCountries;
use Common\Service\Table\Formatter\IrhpPermitRangeTotalPermits;
use Common\Service\Table\Formatter\IrhpPermitRangeType;
use Common\Service\Table\Formatter\IrhpPermitSectorName;
use Common\Service\Table\Formatter\IrhpPermitSectorQuota;
use Common\Service\Table\Formatter\IrhpPermitsRequired;
use Common\Service\Table\Formatter\IrhpPermitStockCountry;
use Common\Service\Table\Formatter\IrhpPermitStockType;
use Common\Service\Table\Formatter\IrhpPermitStockValidity;
use Common\Service\Table\Formatter\IrhpPermitStockValidityFactory;
use Common\Service\Table\Formatter\IrhpPermitType;
use Common\Service\Table\Formatter\IrhpPermitTypeWithValidityDate;
use Common\Service\Table\Formatter\IssuedPermitLicencePermitReference;
use Common\Service\Table\Formatter\LicenceApplication;
use Common\Service\Table\Formatter\LicenceNumberAndStatus;
use Common\Service\Table\Formatter\LicenceNumberLink;
use Common\Service\Table\Formatter\LicencePermitReference;
use Common\Service\Table\Formatter\LicenceStatusSelfserve;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\LvaConditionsUndertakingsTableAttachedTo;
use Common\Service\Table\Formatter\LvaConditionsUndertakingsTableAttachedToFactory;
use Common\Service\Table\Formatter\LvaConditionsUndertakingsTableNo;
use Common\Service\Table\Formatter\LvaConditionsUndertakingsTableNoFormatter;
use Common\Service\Table\Formatter\LvaConditionsUndertakingsTableStatus;
use Common\Service\Table\Formatter\MarkupTableThRemove;
use Common\Service\Table\Formatter\Money;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\NameActionAndStatus;
use Common\Service\Table\Formatter\NameActionAndStatusFactory;
use Common\Service\Table\Formatter\NoteUrl;
use Common\Service\Table\Formatter\NullableNumber;
use Common\Service\Table\Formatter\NumberStackValue;
use Common\Service\Table\Formatter\NumberStackValueFactory;
use Common\Service\Table\Formatter\OcComplaints;
use Common\Service\Table\Formatter\OcConditions;
use Common\Service\Table\Formatter\OcUndertakings;
use Common\Service\Table\Formatter\OpCentreDeltaSum;
use Common\Service\Table\Formatter\OrganisationLink;
use Common\Service\Table\Formatter\AddressFactory;
use Common\Service\Table\Formatter\BusRegNumberLinkFactory;
use Common\Service\Table\Formatter\BusRegStatusFactory;
use Common\Service\Table\Formatter\CaseEntityNrStatusFactory;
use Common\Service\Table\Formatter\CaseLinkFactory;
use Common\Service\Table\Formatter\Comment;
use Common\Service\Table\Formatter\CommunityLicenceStatusFactory;
use Common\Service\Table\Formatter\ConditionsUndertakingsTypeFactory;
use Common\Service\Table\Formatter\ConstrainedCountriesListFactory;
use Common\Service\Table\Formatter\DashboardApplicationLinkFactory;
use Common\Service\Table\Formatter\DashboardTmActionLinkFactory;
use Common\Service\Table\Formatter\DataRetentionAssignedToFactory;
use Common\Service\Table\Formatter\DataRetentionRecordLinkFactory;
use Common\Service\Table\Formatter\DataRetentionRuleAdminLinkFactory;
use Common\Service\Table\Formatter\DataRetentionRuleLinkFactory;
use Common\Service\Table\Formatter\DashboardTmApplicationStatusFactory;
use Common\Service\Table\Formatter\DocumentDescriptionFactory;
use Common\Service\Table\Formatter\EbsrDocumentLinkFactory;
use Common\Service\Table\Formatter\EbsrDocumentStatusFactory;
use Common\Service\Table\Formatter\EbsrRegNumberLinkFactory;
use Common\Service\Table\Formatter\EbsrVariationNumberFactory;
use Common\Service\Table\Formatter\EventHistoryDescriptionFactory;
use Common\Service\Table\Formatter\EventHistoryUserFactory;
use Common\Service\Table\Formatter\ExternalConversationMessage;
use Common\Service\Table\Formatter\FeatureToggleEditLinkFactory;
use Common\Service\Table\Formatter\FeeAmountSumFactory;
use Common\Service\Table\Formatter\FeeIdUrlFactory;
use Common\Service\Table\Formatter\FeeUrlFactory;
use Common\Service\Table\Formatter\FeeUrlExternalFactory;
use Common\Service\Table\Formatter\InspectionRequestIdFactory;
use Common\Service\Table\Formatter\InternalConversationMessage;
use Common\Service\Table\Formatter\InternalLicenceNumberLinkFactory;
use Common\Service\Table\Formatter\InternalLicencePermitReferenceFactory;
use Common\Service\Table\Formatter\IrhpPermitApplicationRefLinkFactory;
use Common\Service\Table\Formatter\IrhpPermitNumberInternalFactory;
use Common\Service\Table\Formatter\IrhpPermitRangePermitNumberFactory;
use Common\Service\Table\Formatter\IrhpPermitRangeTypeFactory;
use Common\Service\Table\Formatter\IrhpPermitStockTypeFactory;
use Common\Service\Table\Formatter\IrhpPermitTypeWithValidityDateFactory;
use Common\Service\Table\Formatter\IssuedPermitLicencePermitReferenceFactory;
use Common\Service\Table\Formatter\LicenceApplicationFactory;
use Common\Service\Table\Formatter\LicenceNumberAndStatusFactory;
use Common\Service\Table\Formatter\LicenceNumberLinkFactory;
use Common\Service\Table\Formatter\LicencePermitReferenceFactory;
use Common\Service\Table\Formatter\LicenceStatusSelfserveFactory;
use Common\Service\Table\Formatter\NameFactory;
use Common\Service\Table\Formatter\NoteUrlFactory;
use Common\Service\Table\Formatter\OrganisationLinkFactory;
use Common\Service\Table\Formatter\PiHearingStatus;
use Common\Service\Table\Formatter\PiReportName;
use Common\Service\Table\Formatter\PiReportNameFactory;
use Common\Service\Table\Formatter\PiReportRecord;
use Common\Service\Table\Formatter\PiReportRecordFactory;
use Common\Service\Table\Formatter\PrinterDocumentCategory;
use Common\Service\Table\Formatter\PrinterDocumentCategoryFactory;
use Common\Service\Table\Formatter\PrinterException;
use Common\Service\Table\Formatter\PublicationNumber;
use Common\Service\Table\Formatter\PublicHolidayArea;
use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\Formatter\RefDataFactory;
use Common\Service\Table\Formatter\RefDataStatus;
use Common\Service\Table\Formatter\RefDataStatusFactory;
use Common\Service\Table\Formatter\SearchAddressComplaint;
use Common\Service\Table\Formatter\SearchAddressComplaintFactory;
use Common\Service\Table\Formatter\SearchAddressOperatorName;
use Common\Service\Table\Formatter\SearchAddressOperatorNameFactory;
use Common\Service\Table\Formatter\SearchAddressOpposition;
use Common\Service\Table\Formatter\SearchAddressOppositionFactory;
use Common\Service\Table\Formatter\SearchApplicationLicenceNo;
use Common\Service\Table\Formatter\SearchApplicationLicenceNoFactory;
use Common\Service\Table\Formatter\SearchBusRegSelfserve;
use Common\Service\Table\Formatter\SearchBusRegSelfserveFactory;
use Common\Service\Table\Formatter\SearchCases;
use Common\Service\Table\Formatter\SearchCasesCaseId;
use Common\Service\Table\Formatter\SearchCasesCaseIdFactory;
use Common\Service\Table\Formatter\SearchCasesFactory;
use Common\Service\Table\Formatter\SearchCasesName;
use Common\Service\Table\Formatter\SearchCasesNameFactory;
use Common\Service\Table\Formatter\SearchIrfoOrganisationOperatorNo;
use Common\Service\Table\Formatter\SearchIrfoOrganisationOperatorNoFactory;
use Common\Service\Table\Formatter\SearchLicenceCaseCount;
use Common\Service\Table\Formatter\SearchLicenceCaseCountFactory;
use Common\Service\Table\Formatter\SearchOperatingCentreSelfserveLicNo;
use Common\Service\Table\Formatter\SearchOperatingCentreSelfserveLicNoFactory;
use Common\Service\Table\Formatter\SearchPeopleName;
use Common\Service\Table\Formatter\SearchPeopleNameFactory;
use Common\Service\Table\Formatter\SearchPeopleRecord;
use Common\Service\Table\Formatter\SearchPeopleRecordFactory;
use Common\Service\Table\Formatter\SeriousInfringementLink;
use Common\Service\Table\Formatter\SeriousInfringementLinkFactory;
use Common\Service\Table\Formatter\SlaTargetDate;
use Common\Service\Table\Formatter\SlaTargetDateFactory;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\StackValueFactory;
use Common\Service\Table\Formatter\StackValueReplacer;
use Common\Service\Table\Formatter\StackValueReplacerFactory;
use Common\Service\Table\Formatter\Sum;
use Common\Service\Table\Formatter\SumColumns;
use Common\Service\Table\Formatter\SystemInfoMessageLink;
use Common\Service\Table\Formatter\SystemInfoMessageLinkFactory;
use Common\Service\Table\Formatter\SystemParameterLink;
use Common\Service\Table\Formatter\SystemParameterLinkFactory;
use Common\Service\Table\Formatter\TaskAllocationCriteria;
use Common\Service\Table\Formatter\TaskAllocationUser;
use Common\Service\Table\Formatter\TaskAllocationUserFactory;
use Common\Service\Table\Formatter\TaskCheckbox;
use Common\Service\Table\Formatter\TaskCheckboxFactory;
use Common\Service\Table\Formatter\TaskDate;
use Common\Service\Table\Formatter\TaskDateFactory;
use Common\Service\Table\Formatter\TaskDescription;
use Common\Service\Table\Formatter\TaskDescriptionFactory;
use Common\Service\Table\Formatter\TaskIdentifier;
use Common\Service\Table\Formatter\TaskIdentifierFactory;
use Common\Service\Table\Formatter\TaskOwner;
use Common\Service\Table\Formatter\TmApplicationManagerType;
use Common\Service\Table\Formatter\TmApplicationManagerTypeFactory;
use Common\Service\Table\Formatter\TransactionAmount;
use Common\Service\Table\Formatter\TransactionAmountSum;
use Common\Service\Table\Formatter\TransactionAmountSumFactory;
use Common\Service\Table\Formatter\TransactionFeeAllocatedAmount;
use Common\Service\Table\Formatter\TransactionFeeStatus;
use Common\Service\Table\Formatter\TransactionFeeStatusFactory;
use Common\Service\Table\Formatter\TransactionNoAndStatus;
use Common\Service\Table\Formatter\TransactionNoAndStatusFactory;
use Common\Service\Table\Formatter\TransactionStatus;
use Common\Service\Table\Formatter\TransactionUrl;
use Common\Service\Table\Formatter\TransactionUrlFactory;
use Common\Service\Table\Formatter\Translate;
use Common\Service\Table\Formatter\TranslateFactory;
use Common\Service\Table\Formatter\TransportManagerDateOfBirth;
use Common\Service\Table\Formatter\TransportManagerDateOfBirthFactory;
use Common\Service\Table\Formatter\TransportManagerName;
use Common\Service\Table\Formatter\TransportManagerNameFactory;
use Common\Service\Table\Formatter\UnlicensedVehicleWeight;
use Common\Service\Table\Formatter\UnlicensedVehicleWeightFactory;
use Common\Service\Table\Formatter\ValidityPeriod;
use Common\Service\Table\Formatter\ValidityPeriodFactory;
use Common\Service\Table\Formatter\ValidPermitCount;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Common\Service\Table\Formatter\VehicleLink;
use Common\Service\Table\Formatter\VehicleLinkFactory;
use Common\Service\Table\Formatter\VehicleRegistrationMark;
use Common\Service\Table\Formatter\VehicleRegistrationMarkFactory;
use Common\Service\Table\Formatter\VenueAddress;
use Common\Service\Table\Formatter\VenueAddressFactory;
use Common\Service\Table\Formatter\YesNo;
use Common\Service\Table\Formatter\YesNoFactory;

return [
    'invokables' => [
        CaseEntityName::class => CaseEntityName::class,
        CaseTrafficArea::class => CaseTrafficArea::class,
        Comment::class => Comment::class,
        CommunityLicenceIssueNo::class => CommunityLicenceIssueNo::class,
        ConvictionDescription::class => ConvictionDescription::class,
        DataRetentionRuleActionType::class => DataRetentionRuleActionType::class,
        DataRetentionRuleIsEnabled::class => DataRetentionRuleIsEnabled::class,
        Date::class => Date::class,
        DateTime::class => DateTime::class,
        DocumentSubcategory::class => DocumentSubcategory::class,
        ExternalConversationMessage::class => ExternalConversationMessage::class,
        FeeAmount::class => FeeAmount::class,
        FeeStatus::class => FeeStatus::class,
        FileExtension::class => FileExtension::class,
        HideIfClosedRadio::class => HideIfClosedRadio::class,
        InterimOcCheckbox::class => InterimOcCheckbox::class,
        InterimVehiclesCheckbox::class => InterimVehiclesCheckbox::class,
        InternalConversationMessage::class => InternalConversationMessage::class,
        IrhpPermitJurisdictionPermitNumber::class => IrhpPermitJurisdictionPermitNumber::class,
        IrhpPermitJurisdictionTrafficArea::class => IrhpPermitJurisdictionTrafficArea::class,
        IrhpPermitOrganisationName::class => IrhpPermitOrganisationName::class,
        IrhpPermitRangeReplacement::class => IrhpPermitRangeReplacement::class,
        IrhpPermitRangeReserve::class => IrhpPermitRangeReserve::class,
        IrhpPermitRangeRestrictedCountries::class => IrhpPermitRangeRestrictedCountries::class,
        IrhpPermitRangeTotalPermits::class => IrhpPermitRangeTotalPermits::class,
        IrhpPermitSectorName::class => IrhpPermitSectorName::class,
        IrhpPermitSectorQuota::class => IrhpPermitSectorQuota::class,
        IrhpPermitsRequired::class => IrhpPermitsRequired::class,
        IrhpPermitStockCountry::class => IrhpPermitStockCountry::class,
        IrhpPermitType::class => IrhpPermitType::class,
        LicenceTypeShort::class => LicenceTypeShort::class,
        Money::class => Money::class,
        NullableNumber::class => NullableNumber::class,
        OcComplaints::class => OcComplaints::class,
        OcConditions::class => OcConditions::class,
        OcUndertakings::class => OcUndertakings::class,
        OpCentreDeltaSum::class => OpCentreDeltaSum::class,
        PiHearingStatus::class => PiHearingStatus::class,
        PrinterException::class => PrinterException::class,
        PublicationNumber::class => PublicationNumber::class,
        PublicHolidayArea::class => PublicHolidayArea::class,
        SumColumns::class => SumColumns::class,
        Sum::class => Sum::class,
        TaskAllocationCriteria::class => TaskAllocationCriteria::class,
        TaskOwner::class => TaskOwner::class,
        TransactionAmount::class => TransactionAmount::class,
        TransactionFeeAllocatedAmount::class => TransactionFeeAllocatedAmount::class,
        TransactionStatus::class => TransactionStatus::class,
        ValidPermitCount::class => ValidPermitCount::class,
        VehicleDiscNo::class => VehicleDiscNo::class,
    ],
    'factories' => [
        AccessedCorrespondence::class => AccessedCorrespondenceFactory::class,
        Address::class => AddressFactory::class,
        AddressLines::class => AddressFactory::class,
        BusRegNumberLink::class => BusRegNumberLinkFactory::class,
        BusRegStatus::class => BusRegStatusFactory::class,
        CaseEntityNrStatus::class => CaseEntityNrStatusFactory::class,
        CaseLink::class => CaseLinkFactory::class,
        CommunityLicenceStatus::class => CommunityLicenceStatusFactory::class,
        ConditionsUndertakingsType::class => ConditionsUndertakingsTypeFactory::class,
        ConstrainedCountriesList::class => ConstrainedCountriesListFactory::class,
        DashboardApplicationLink::class => DashboardApplicationLinkFactory::class,
        DashboardTmActionLink::class => DashboardTmActionLinkFactory::class,
        DataRetentionAssignedTo::class => DataRetentionAssignedToFactory::class,
        DataRetentionRecordLink::class => DataRetentionRecordLinkFactory::class,
        DataRetentionRuleAdminLink::class => DataRetentionRuleAdminLinkFactory::class,
        DataRetentionRuleLink::class => DataRetentionRuleLinkFactory::class,
        DashboardTmApplicationStatus::class => DashboardTmApplicationStatusFactory::class,
        DisqualifyUrl::class => DisqualifyUrlFactory::class,
        DocumentDescription::class => DocumentDescriptionFactory::class,
        EbsrDocumentLink::class => EbsrDocumentLinkFactory::class,
        EbsrDocumentStatus::class => EbsrDocumentStatusFactory::class,
        EbsrRegNumberLink::class => EbsrRegNumberLinkFactory::class,
        EbsrVariationNumber::class => EbsrVariationNumberFactory::class,
        EventHistoryDescription::class => EventHistoryDescriptionFactory::class,
        EventHistoryUser::class => EventHistoryUserFactory::class,
        ExternalConversationLink::class => ExternalConversationLinkFactory::class,
        ExternalConversationStatus::class => ExternalConversationStatusFactory::class,
        FeatureToggleEditLink::class              => FeatureToggleEditLinkFactory::class,
        FeeAmountSum::class                       => FeeAmountSumFactory::class,
        FeeIdUrl::class                           => FeeIdUrlFactory::class,
        FeeNoAndStatus::class                     => FeeNoAndStatusFactory::class,
        FeeTransactionDate::class                 => FeeTransactionDateFactory::class,
        FeeUrl::class                             => FeeUrlFactory::class,
        FeeUrlExternal::class                     => FeeUrlExternalFactory::class,
        InspectionRequestId::class                => InspectionRequestIdFactory::class,
        InternalLicenceNumberLink::class          => InternalLicenceNumberLinkFactory::class,
        InternalLicencePermitReference::class     => InternalLicencePermitReferenceFactory::class,
        InternalConversationLink::class           => InternalConversationLinkFactory::class,
        IrhpPermitApplicationRefLink::class       => IrhpPermitApplicationRefLinkFactory::class,
        IrhpPermitNumberInternal::class           => IrhpPermitNumberInternalFactory::class,
        IrhpPermitRangePermitNumber::class        => IrhpPermitRangePermitNumberFactory::class,
        IrhpPermitRangeType::class                => IrhpPermitRangeTypeFactory::class,
        IrhpPermitStockType::class                => IrhpPermitStockTypeFactory::class,
        IrhpPermitStockValidity::class            => IrhpPermitStockValidityFactory::class,
        IrhpPermitTypeWithValidityDate::class     => IrhpPermitTypeWithValidityDateFactory::class,
        IssuedPermitLicencePermitReference::class => IssuedPermitLicencePermitReferenceFactory::class,
        LicenceApplication::class                 => LicenceApplicationFactory::class,
        LicenceNumberAndStatus::class             => LicenceNumberAndStatusFactory::class,
        LicenceNumberLink::class => LicenceNumberLinkFactory::class,
        LicencePermitReference::class => LicencePermitReferenceFactory::class,
        LicenceStatusSelfserve::class => LicenceStatusSelfserveFactory::class,
        Name::class => NameFactory::class,
        NameActionAndStatus::class => NameActionAndStatusFactory::class,
        NumberStackValue::class => NumberStackValueFactory::class,
        NoteUrl::class => NoteUrlFactory::class,
        OrganisationLink::class => OrganisationLinkFactory::class,
        PiReportName::class => PiReportNameFactory::class,
        PiReportRecord::class => PiReportRecordFactory::class,
        PrinterDocumentCategory::class => PrinterDocumentCategoryFactory::class,
        RefData::class => RefDataFactory::class,
        RefDataStatus::class => RefDataStatusFactory::class,
        SearchAddressComplaint::class => SearchAddressComplaintFactory::class,
        SearchAddressOperatorName::class => SearchAddressOperatorNameFactory::class,
        SearchAddressOpposition::class => SearchAddressOppositionFactory::class,
        SearchApplicationLicenceNo::class => SearchApplicationLicenceNoFactory::class,
        SearchBusRegSelfserve::class => SearchBusRegSelfserveFactory::class,
        SearchCasesCaseId::class => SearchCasesCaseIdFactory::class,
        SearchCasesName::class => SearchCasesNameFactory::class,
        SearchOperatingCentreSelfserveLicNo::class => SearchOperatingCentreSelfserveLicNoFactory::class,
        SearchIrfoOrganisationOperatorNo::class => SearchIrfoOrganisationOperatorNoFactory::class,
        SearchLicenceCaseCount::class => SearchLicenceCaseCountFactory::class,
        SearchPeopleRecord::class => SearchPeopleRecordFactory::class,
        SearchPeopleName::class => SearchPeopleNameFactory::class,
        SeriousInfringementLink::class => SeriousInfringementLinkFactory::class,
        SlaTargetDate::class => SlaTargetDateFactory::class,
        StackValue::class => StackValueFactory::class,
        StackValueReplacer::class => StackValueReplacerFactory::class,
        SystemInfoMessageLink::class => SystemInfoMessageLinkFactory::class,
        SystemParameterLink::class => SystemParameterLinkFactory::class,
        TaskAllocationUser::class => TaskAllocationUserFactory::class,
        TaskCheckbox::class => TaskCheckboxFactory::class,
        TaskDate::class => TaskDateFactory::class,
        TaskDescription::class => TaskDescriptionFactory::class,
        TaskIdentifier::class => TaskIdentifierFactory::class,
        TmApplicationManagerType::class => TmApplicationManagerTypeFactory::class,
        TransactionAmountSum::class => TransactionAmountSumFactory::class,
        TransactionFeeStatus::class => TransactionFeeStatusFactory::class,
        TransactionNoAndStatus::class => TransactionNoAndStatusFactory::class,
        TransactionUrl::class => TransactionUrlFactory::class,
        Translate::class => TranslateFactory::class,
        TransportManagerDateOfBirth::class => TransportManagerDateOfBirthFactory::class,
        TransportManagerName::class => TransportManagerNameFactory::class,
        UnlicensedVehicleWeight::class => UnlicensedVehicleWeightFactory::class,
        ValidityPeriod::class => ValidityPeriodFactory::class,
        VehicleLink::class => VehicleLinkFactory::class,
        VehicleRegistrationMark::class => VehicleRegistrationMarkFactory::class,
        VenueAddress::class => VenueAddressFactory::class,
        Vrm::class => VrmFactory::class,
        YesNo::class => YesNoFactory::class,
    ],
];
