<?php

use Dvsa\Olcs\Snapshot\Service\Snapshots;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section as Review;
use Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section as TmReview;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section as ContinuationReview;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section as SurrenderReview;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Permits\IrhpGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Permits\IrhpGeneratorFactory;

return [
    'service_manager' => [
        'factories' => [
            ContinuationReview\TypeOfLicenceReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\BusinessTypeReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\BusinessDetailsReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\AddressesReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\PeopleReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\VehiclesReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\UsersReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\OperatingCentresReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\TransportManagersReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\SafetyReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\DeclarationReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\FinanceReviewService::class => ContinuationReview\FinanceReviewServiceFactory::class,
            ContinuationReview\ConditionsUndertakingsReviewService::class => ContinuationReview\GenericFactory::class,
            ContinuationReview\AbstractReviewServiceServices::class => ContinuationReview\AbstractReviewServiceServicesFactory::class,
            IrhpGenerator::class => IrhpGeneratorFactory::class,
            'ContinuationReview' => Snapshots\ContinuationReview\GeneratorFactory::class,
            'ReviewSnapshot' => Snapshots\ApplicationReview\GeneratorFactory::class,
            Snapshots\Messaging\Generator::class => Snapshots\Messaging\GeneratorFactory::class,
            Snapshots\Messaging\EnhancedGenerator::class => Snapshots\Messaging\EnhancedGeneratorFactory::class,
            Review\VariationTypeOfLicenceReviewService::class => Review\GenericFactory::class,
            Review\VariationBusinessTypeReviewService::class => Review\GenericFactory::class,
            Review\VariationFinancialEvidenceReviewService::class => Review\VariationFinancialEvidenceReviewServiceFactory::class,
            Review\ApplicationFinancialEvidenceReviewService::class => Review\ApplicationFinancialEvidenceReviewServiceFactory::class,
            Review\VariationLicenceHistoryReviewService::class => Review\VariationLicenceHistoryReviewServiceFactory::class,
            Review\ApplicationSafetyReviewService::class => Review\GenericFactory::class,
            Review\VariationBusinessDetailsReviewService::class => Review\GenericFactory::class,
            Review\VariationAddressesReviewService::class => Review\GenericFactory::class,
            Review\ApplicationOperatingCentresReviewService::class => Review\ApplicationOperatingCentresReviewServiceFactory::class,
            Review\VariationPsvOcTotalAuthReviewService::class => Review\GenericFactory::class,
            Review\VariationSafetyReviewService::class => Review\GenericFactory::class,
            Review\ApplicationBusinessDetailsReviewService::class => Review\GenericFactory::class,
            Review\ApplicationTypeOfLicenceReviewService::class => Review\GenericFactory::class,
            Review\GoodsOperatingCentreReviewService::class => Review\GoodsOperatingCentreReviewServiceFactory::class,
            Review\ApplicationConditionsUndertakingsReviewService::class => Review\ApplicationConditionsUndertakingsReviewServiceFactory::class,
            Review\ConditionsUndertakingsReviewService::class => Review\GenericFactory::class,
            Review\ApplicationGoodsOcTotalAuthReviewService::class => Review\GenericFactory::class,
            Review\VariationPeopleReviewService::class => Review\VariationPeopleReviewServiceFactory::class,
            Review\VehiclesPsvReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvOcTotalAuthReviewService::class => Review\GenericFactory::class,
            Review\VariationConditionsUndertakingsReviewService::class => Review\VariationConditionsUndertakingsReviewServiceFactory::class,
            Review\ApplicationBusinessTypeReviewService::class => Review\GenericFactory::class,
            Review\VariationOperatingCentresReviewService::class => Review\VariationOperatingCentresReviewServiceFactory::class,
            Review\ApplicationFinancialHistoryReviewService::class => Review\GenericFactory::class,
            Review\VariationDiscsReviewService::class => Review\GenericFactory::class,
            Review\VariationTransportManagersReviewService::class => Review\VariationTransportManagersReviewServiceFactory::class,
            Review\PsvOperatingCentreReviewService::class => Review\GenericFactory::class,
            Review\VariationConvictionsPenaltiesReviewService::class => Review\VariationConvictionsPenaltiesReviewServiceFactory::class,
            Review\ApplicationLicenceHistoryReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPeopleReviewService::class => Review\ApplicationPeopleReviewServiceFactory::class,
            Review\TransportManagersReviewService::class => Review\GenericFactory::class,
            Review\TrafficAreaReviewService::class => Review\GenericFactory::class,
            Review\VariationFinancialHistoryReviewService::class => Review\VariationFinancialHistoryReviewServiceFactory::class,
            Review\LicenceConditionsUndertakingsReviewService::class => Review\LicenceConditionsUndertakingsReviewServiceFactory::class,
            Review\VariationVehiclesPsvReviewService::class => Review\VariationVehiclesPsvReviewServiceFactory::class,
            Review\ApplicationVehiclesReviewService::class => Review\GenericFactory::class,
            Review\ApplicationConvictionsPenaltiesReviewService::class => Review\GenericFactory::class,
            Review\ApplicationTransportManagersReviewService::class => Review\ApplicationTransportManagersReviewServiceFactory::class,
            Review\VariationGoodsOcTotalAuthReviewService::class => Review\GenericFactory::class,
            Review\PeopleReviewService::class => Review\GenericFactory::class,
            Review\ApplicationVehiclesPsvReviewService::class => Review\ApplicationVehiclesPsvReviewServiceFactory::class,
            Review\ApplicationAddressesReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvDocumentaryEvidenceLargeReviewService::class => Review\EvidenceReviewServiceFactory::class,
            Review\ApplicationPsvDocumentaryEvidenceSmallReviewService::class => Review\EvidenceReviewServiceFactory::class,
            Review\ApplicationPsvSmallPartWrittenReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvMainOccupationUndertakingsReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvOperateNoveltyReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvSmallConditionsReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvOperateSmallReviewService::class => Review\GenericFactory::class,
            Review\ApplicationPsvOperateLargeReviewService::class => Review\GenericFactory::class,
            Review\ApplicationVehiclesSizeReviewService::class => Review\GenericFactory::class,
            Review\VariationPsvDocumentaryEvidenceLargeReviewService::class => Review\EvidenceReviewServiceFactory::class,
            Review\VariationPsvDocumentaryEvidenceSmallReviewService::class => Review\EvidenceReviewServiceFactory::class,
            Review\VariationPsvSmallPartWrittenReviewService::class => Review\GenericFactory::class,
            Review\VariationPsvMainOccupationUndertakingsReviewService::class => Review\GenericFactory::class,
            Review\VariationPsvOperateNoveltyReviewService::class => Review\GenericFactory::class,
            Review\VariationPsvSmallConditionsReviewService::class => Review\GenericFactory::class,
            Review\VariationPsvOperateSmallReviewService::class => Review\GenericFactory::class,
            Review\VariationPsvOperateLargeReviewService::class => Review\GenericFactory::class,
            Review\VariationVehiclesSizeReviewService::class => Review\GenericFactory::class,
            Review\VariationVehiclesReviewService::class => Review\GenericFactory::class,
            Review\ApplicationUndertakingsReviewService::class => Review\GenericFactory::class,
            Review\VariationUndertakingsReviewService::class => Review\GenericFactory::class,
            Review\SignatureReviewService::class => Review\GenericFactory::class,
            Review\AbstractReviewServiceServices::class => Review\AbstractReviewServiceServicesFactory::class,
            TmReview\TransportManagerMainReviewService::class => TmReview\GenericFactory::class,
            TmReview\TransportManagerResponsibilityReviewService::class => TmReview\GenericFactory::class,
            TmReview\TransportManagerOtherEmploymentReviewService::class => TmReview\GenericFactory::class,
            TmReview\TransportManagerPreviousConvictionReviewService::class => TmReview\GenericFactory::class,
            TmReview\TransportManagerPreviousLicenceReviewService::class => TmReview\GenericFactory::class,
            TmReview\TransportManagerDeclarationReviewService::class => TmReview\GenericFactory::class,
            TmReview\TransportManagerSignatureReviewService::class => TmReview\GenericFactory::class,
            TmReview\AbstractReviewServiceServices::class => TmReview\AbstractReviewServiceServicesFactory::class,
            'TmReviewSnapshot' => \Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\GeneratorFactory::class,
            Snapshots\AbstractGeneratorServices::class => Snapshots\AbstractGeneratorServicesFactory::class,
            Snapshots\Surrender\Generator::class => Snapshots\Surrender\GeneratorFactory::class,
            SurrenderReview\AbstractReviewServiceServices::class => SurrenderReview\AbstractReviewServiceServicesFactory::class,
            SurrenderReview\LicenceDetailsService::class => SurrenderReview\GenericFactory::class,
            SurrenderReview\CurrentDiscsReviewService::class => SurrenderReview\GenericFactory::class,
            SurrenderReview\OperatorLicenceReviewService::class => SurrenderReview\GenericFactory::class,
            SurrenderReview\DeclarationReviewService::class => SurrenderReview\GenericFactory::class,
            SurrenderReview\SignatureReviewService::class => SurrenderReview\GenericFactory::class,
            SurrenderReview\CommunityLicenceReviewService::class => SurrenderReview\GenericFactory::class,
        ],
        'aliases' => [
            'ContinuationReview\TypeOfLicence' => ContinuationReview\TypeOfLicenceReviewService::class,
            'ContinuationReview\BusinessType' => ContinuationReview\BusinessTypeReviewService::class,
            'ContinuationReview\BusinessDetails' => ContinuationReview\BusinessDetailsReviewService::class,
            'ContinuationReview\Addresses' => ContinuationReview\AddressesReviewService::class,
            'ContinuationReview\People' => ContinuationReview\PeopleReviewService::class,
            'ContinuationReview\Vehicles' => ContinuationReview\VehiclesReviewService::class,
            'ContinuationReview\Users' => ContinuationReview\UsersReviewService::class,
            'ContinuationReview\VehiclesPsv' => ContinuationReview\VehiclesReviewService::class,
            'ContinuationReview\OperatingCentres' => ContinuationReview\OperatingCentresReviewService::class,
            'ContinuationReview\TransportManagers' => ContinuationReview\TransportManagersReviewService::class,
            'ContinuationReview\Safety' => ContinuationReview\SafetyReviewService::class,
            'ContinuationReview\Declaration' => ContinuationReview\DeclarationReviewService::class,
            'ContinuationReview\Finance' => ContinuationReview\FinanceReviewService::class,
            'ContinuationReview\ConditionsUndertakings' =>
                ContinuationReview\ConditionsUndertakingsReviewService::class,
            'Review\VariationTypeOfLicence' => Review\VariationTypeOfLicenceReviewService::class,
            'Review\VariationBusinessType' => Review\VariationBusinessTypeReviewService::class,
            'Review\VariationFinancialEvidence' => Review\VariationFinancialEvidenceReviewService::class,
            'Review\ApplicationFinancialEvidence' => Review\ApplicationFinancialEvidenceReviewService::class,
            'Review\VariationLicenceHistory' => Review\VariationLicenceHistoryReviewService::class,
            'Review\ApplicationSafety' => Review\ApplicationSafetyReviewService::class,
            'Review\VariationBusinessDetails' => Review\VariationBusinessDetailsReviewService::class,
            'Review\VariationAddresses' => Review\VariationAddressesReviewService::class,
            'Review\ApplicationOperatingCentres' => Review\ApplicationOperatingCentresReviewService::class,
            'Review\VariationPsvOcTotalAuth' => Review\VariationPsvOcTotalAuthReviewService::class,
            'Review\VariationSafety' => Review\VariationSafetyReviewService::class,
            'Review\ApplicationBusinessDetails' => Review\ApplicationBusinessDetailsReviewService::class,
            'Review\ApplicationTypeOfLicence' => Review\ApplicationTypeOfLicenceReviewService::class,
            'Review\GoodsOperatingCentre' => Review\GoodsOperatingCentreReviewService::class,
            'Review\ApplicationConditionsUndertakings' => Review\ApplicationConditionsUndertakingsReviewService::class,
            'Review\ConditionsUndertakings' => Review\ConditionsUndertakingsReviewService::class,
            'Review\ApplicationGoodsOcTotalAuth' => Review\ApplicationGoodsOcTotalAuthReviewService::class,
            'Review\VariationPeople' => Review\VariationPeopleReviewService::class,
            'Review\VehiclesPsv' => Review\VehiclesPsvReviewService::class,
            'Review\ApplicationPsvOcTotalAuth' => Review\ApplicationPsvOcTotalAuthReviewService::class,
            'Review\VariationConditionsUndertakings' => Review\VariationConditionsUndertakingsReviewService::class,
            'Review\ApplicationBusinessType' => Review\ApplicationBusinessTypeReviewService::class,
            'Review\VariationOperatingCentres' => Review\VariationOperatingCentresReviewService::class,
            'Review\ApplicationFinancialHistory' => Review\ApplicationFinancialHistoryReviewService::class,
            'Review\VariationDiscs' => Review\VariationDiscsReviewService::class,
            'Review\VariationTransportManagers' => Review\VariationTransportManagersReviewService::class,
            'Review\PsvOperatingCentre' => Review\PsvOperatingCentreReviewService::class,
            'Review\VariationConvictionsPenalties' => Review\VariationConvictionsPenaltiesReviewService::class,
            'Review\ApplicationLicenceHistory' => Review\ApplicationLicenceHistoryReviewService::class,
            'Review\ApplicationPeople' => Review\ApplicationPeopleReviewService::class,
            'Review\TransportManagers' => Review\TransportManagersReviewService::class,
            'Review\TrafficArea' => Review\TrafficAreaReviewService::class,
            'Review\VariationFinancialHistory' => Review\VariationFinancialHistoryReviewService::class,
            'Review\LicenceConditionsUndertakings' => Review\LicenceConditionsUndertakingsReviewService::class,
            'Review\VariationVehiclesPsv' => Review\VariationVehiclesPsvReviewService::class,
            'Review\ApplicationVehicles' => Review\ApplicationVehiclesReviewService::class,
            'Review\ApplicationConvictionsPenalties' => Review\ApplicationConvictionsPenaltiesReviewService::class,
            'Review\ApplicationTransportManagers' => Review\ApplicationTransportManagersReviewService::class,
            'Review\VariationGoodsOcTotalAuth' => Review\VariationGoodsOcTotalAuthReviewService::class,
            'Review\People' => Review\PeopleReviewService::class,
            'Review\ApplicationVehiclesPsv' => Review\ApplicationVehiclesPsvReviewService::class,
            'Review\ApplicationAddresses' => Review\ApplicationAddressesReviewService::class,
            'Review\ApplicationTaxiPhv' => Review\ApplicationTaxiPhvReviewService::class,
            'Review\ApplicationPsvOperateSmall' => Review\ApplicationPsvOperateSmallReviewService::class,
            'Review\ApplicationPsvOperateLarge' => Review\ApplicationPsvOperateLargeReviewService::class,
            'Review\ApplicationVehiclesSize' => Review\ApplicationVehiclesSizeReviewService::class,
            'Review\ApplicationPsvSmallConditions' => Review\ApplicationPsvSmallConditionsReviewService::class,
            'Review\ApplicationPsvOperateNovelty' => Review\ApplicationPsvOperateNoveltyReviewService::class,
            'Review\ApplicationPsvDocumentaryEvidenceLarge' => Review\ApplicationPsvDocumentaryEvidenceLargeReviewService::class,
            'Review\ApplicationPsvDocumentaryEvidenceSmall' => Review\ApplicationPsvDocumentaryEvidenceSmallReviewService::class,
            'Review\ApplicationPsvMainOccupationUndertakings' => Review\ApplicationPsvMainOccupationUndertakingsReviewService::class,
            'Review\ApplicationPsvSmallPartWritten' => Review\ApplicationPsvSmallPartWrittenReviewService::class,
            'Review\VariationPsvOperateSmall' => Review\VariationPsvOperateSmallReviewService::class,
            'Review\VariationPsvOperateLarge' => Review\VariationPsvOperateLargeReviewService::class,
            'Review\VariationVehiclesSize' => Review\VariationVehiclesSizeReviewService::class,
            'Review\VariationPsvSmallConditions' => Review\VariationPsvSmallConditionsReviewService::class,
            'Review\VariationPsvOperateNovelty' => Review\VariationPsvOperateNoveltyReviewService::class,
            'Review\VariationPsvDocumentaryEvidenceLarge' => Review\VariationPsvDocumentaryEvidenceLargeReviewService::class,
            'Review\VariationPsvDocumentaryEvidenceSmall' => Review\VariationPsvDocumentaryEvidenceSmallReviewService::class,
            'Review\VariationPsvMainOccupationUndertakings' => Review\VariationPsvMainOccupationUndertakingsReviewService::class,
            'Review\VariationPsvSmallPartWritten' => Review\VariationPsvSmallPartWrittenReviewService::class,
            'Review\VariationVehicles' => Review\VariationVehiclesReviewService::class,
            'Review\ApplicationUndertakings' => Review\ApplicationUndertakingsReviewService::class,
            'Review\VariationUndertakings' => Review\VariationUndertakingsReviewService::class,
            'Review\TransportManagerMain' => TmReview\TransportManagerMainReviewService::class,
            'Review\TransportManagerResponsibility' => TmReview\TransportManagerResponsibilityReviewService::class,
            'Review\TransportManagerOtherEmployment' => TmReview\TransportManagerOtherEmploymentReviewService::class,
            'Review\TransportManagerPreviousConviction'
                => TmReview\TransportManagerPreviousConvictionReviewService::class,
            'Review\TransportManagerPreviousLicence' => TmReview\TransportManagerPreviousLicenceReviewService::class,
            'Review\TransportManagerDeclaration' => TmReview\TransportManagerDeclarationReviewService::class,
            'Review\TransportManagerSignature' => TmReview\TransportManagerSignatureReviewService::class,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
            'translations' => __DIR__ . '/language/partials'
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'answerFormatter' => Dvsa\Olcs\Snapshot\View\Helper\AnswerFormatter::class,
        ],
    ],
];
