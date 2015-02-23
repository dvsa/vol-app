<?php

return array(
    'router' => [
        'routes' => include __DIR__ . '/routes.config.php'
    ],
    'tables' => array(
        'config' => array(
            __DIR__ . '/../src/Table/Tables/'
        )
    ),
    'controllers' => array(
        'initializers' => array(
            'Olcs\Controller\RouteParamInitializer'
        ),
        'lva_controllers' => array(
            'LvaApplication' => 'Olcs\Controller\Lva\Application\OverviewController',
            'LvaApplication/TypeOfLicence' => 'Olcs\Controller\Lva\Application\TypeOfLicenceController',
            'LvaApplication/BusinessType' => 'Olcs\Controller\Lva\Application\BusinessTypeController',
            'LvaApplication/BusinessDetails' => 'Olcs\Controller\Lva\Application\BusinessDetailsController',
            'LvaApplication/Addresses' => 'Olcs\Controller\Lva\Application\AddressesController',
            'LvaApplication/People' => 'Olcs\Controller\Lva\Application\PeopleController',
            'LvaApplication/OperatingCentres' => 'Olcs\Controller\Lva\Application\OperatingCentresController',
            'LvaApplication/FinancialEvidence' => 'Olcs\Controller\Lva\Application\FinancialEvidenceController',
            'LvaApplication/TransportManagers' => 'Olcs\Controller\Lva\Application\TransportManagersController',
            'LvaApplication/Vehicles' => 'Olcs\Controller\Lva\Application\VehiclesController',
            'LvaApplication/VehiclesPsv' => 'Olcs\Controller\Lva\Application\VehiclesPsvController',
            'LvaApplication/Safety' => 'Olcs\Controller\Lva\Application\SafetyController',
            'LvaApplication/CommunityLicences' => 'Olcs\Controller\Lva\Application\CommunityLicencesController',
            'LvaApplication/FinancialHistory' => 'Olcs\Controller\Lva\Application\FinancialHistoryController',
            'LvaApplication/LicenceHistory' => 'Olcs\Controller\Lva\Application\LicenceHistoryController',
            'LvaApplication/ConvictionsPenalties' => 'Olcs\Controller\Lva\Application\ConvictionsPenaltiesController',
            'LvaApplication/TaxiPhv' => 'Olcs\Controller\Lva\Application\TaxiPhvController',
            'LvaApplication/ConditionsUndertakings'
                => 'Olcs\Controller\Lva\Application\ConditionsUndertakingsController',
            'LvaApplication/VehiclesDeclarations' => 'Olcs\Controller\Lva\Application\VehiclesDeclarationsController',
            'LvaApplication/Review' => 'Olcs\Controller\Lva\Application\ReviewController',
            'LvaLicence' => 'Olcs\Controller\Lva\Licence\OverviewController',
            'LvaLicence/TypeOfLicence' => 'Olcs\Controller\Lva\Licence\TypeOfLicenceController',
            'LvaLicence/BusinessType' => 'Olcs\Controller\Lva\Licence\BusinessTypeController',
            'LvaLicence/BusinessDetails' => 'Olcs\Controller\Lva\Licence\BusinessDetailsController',
            'LvaLicence/Addresses' => 'Olcs\Controller\Lva\Licence\AddressesController',
            'LvaLicence/People' => 'Olcs\Controller\Lva\Licence\PeopleController',
            'LvaLicence/OperatingCentres' => 'Olcs\Controller\Lva\Licence\OperatingCentresController',
            'LvaLicence/TransportManagers' => 'Olcs\Controller\Lva\Licence\TransportManagersController',
            'LvaLicence/Vehicles' => 'Olcs\Controller\Lva\Licence\VehiclesController',
            'LvaLicence/VehiclesPsv' => 'Olcs\Controller\Lva\Licence\VehiclesPsvController',
            'LvaLicence/Safety' => 'Olcs\Controller\Lva\Licence\SafetyController',
            'LvaLicence/CommunityLicences' => 'Olcs\Controller\Lva\Licence\CommunityLicencesController',
            'LvaLicence/TaxiPhv' => 'Olcs\Controller\Lva\Licence\TaxiPhvController',
            'LvaLicence/Discs' => 'Olcs\Controller\Lva\Licence\DiscsController',
            'LvaLicence/ConditionsUndertakings' => 'Olcs\Controller\Lva\Licence\ConditionsUndertakingsController',
            'LvaVariation' => 'Olcs\Controller\Lva\Variation\OverviewController',
            'LvaVariation/TypeOfLicence' => 'Olcs\Controller\Lva\Variation\TypeOfLicenceController',
            'LvaVariation/BusinessType' => 'Olcs\Controller\Lva\Variation\BusinessTypeController',
            'LvaVariation/BusinessDetails' => 'Olcs\Controller\Lva\Variation\BusinessDetailsController',
            'LvaVariation/Addresses' => 'Olcs\Controller\Lva\Variation\AddressesController',
            'LvaVariation/People' => 'Olcs\Controller\Lva\Variation\PeopleController',
            'LvaVariation/OperatingCentres' => 'Olcs\Controller\Lva\Variation\OperatingCentresController',
            'LvaVariation/TransportManagers' => 'Olcs\Controller\Lva\Variation\TransportManagersController',
            'LvaVariation/Vehicles' => 'Olcs\Controller\Lva\Variation\VehiclesController',
            'LvaVariation/VehiclesPsv' => 'Olcs\Controller\Lva\Variation\VehiclesPsvController',
            'LvaVariation/Safety' => 'Olcs\Controller\Lva\Variation\SafetyController',
            'LvaVariation/CommunityLicences' => 'Olcs\Controller\Lva\Variation\CommunityLicencesController',
            'LvaVariation/TaxiPhv' => 'Olcs\Controller\Lva\Variation\TaxiPhvController',
            'LvaVariation/Discs' => 'Olcs\Controller\Lva\Variation\DiscsController',
            'LvaVariation/ConditionsUndertakings' => 'Olcs\Controller\Lva\Variation\ConditionsUndertakingsController',
            'LvaVariation/FinancialEvidence' => 'Olcs\Controller\Lva\Variation\FinancialEvidenceController',
            'LvaVariation/FinancialHistory' => 'Olcs\Controller\Lva\Variation\FinancialHistoryController',
            'LvaVariation/ConvictionsPenalties' => 'Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController',
            'LvaVariation/VehiclesDeclarations' => 'Olcs\Controller\Lva\Variation\VehiclesDeclarationsController',
            'LvaVariation/Review' => 'Olcs\Controller\Lva\Variation\ReviewController',
        ),
        'invokables' => array(
            'CaseController' => 'Olcs\Controller\Cases\CaseController',
            'CaseOppositionController' => 'Olcs\Controller\Cases\Opposition\OppositionController',
            'CaseStatementController' => 'Olcs\Controller\Cases\Statement\StatementController',
            'CaseHearingAppealController' => 'Olcs\Controller\Cases\Hearing\HearingAppealController',
            'CaseAppealController' => 'Olcs\Controller\Cases\Hearing\AppealController',
            'CaseComplaintController' => 'Olcs\Controller\Cases\Complaint\ComplaintController',
            'CaseEnvironmentalComplaintController' =>
                'Olcs\Controller\Cases\Complaint\EnvironmentalComplaintController',
            'CaseConvictionController' => 'Olcs\Controller\Cases\Conviction\ConvictionController',
            'CaseSeriousInfringementController' =>
                'Olcs\Controller\Cases\SeriousInfringement\SeriousInfringementController',
            'CaseOffenceController' => 'Olcs\Controller\Cases\Conviction\OffenceController',
            'CaseSubmissionController' => 'Olcs\Controller\Cases\Submission\SubmissionController',
            'CaseSubmissionSectionCommentController'
            => 'Olcs\Controller\Cases\Submission\SubmissionSectionCommentController',
            'CaseSubmissionRecommendationController'
            => 'Olcs\Controller\Cases\Submission\RecommendationController',
            'CaseSubmissionDecisionController'
            => 'Olcs\Controller\Cases\Submission\DecisionController',
            'CaseStayController' => 'Olcs\Controller\Cases\Hearing\StayController',
            'CasePenaltyController' => 'Olcs\Controller\Cases\Penalty\PenaltyController',
            'CaseAppliedPenaltyController' => 'Olcs\Controller\Cases\Penalty\AppliedPenaltyController',
            'CaseProhibitionController' => 'Olcs\Controller\Cases\Prohibition\ProhibitionController',
            'CaseProhibitionDefectController' => 'Olcs\Controller\Cases\Prohibition\ProhibitionDefectController',
            'CaseAnnualTestHistoryController' => 'Olcs\Controller\Cases\AnnualTestHistory\AnnualTestHistoryController',
            'CaseImpoundingController' => 'Olcs\Controller\Cases\Impounding\ImpoundingController',
            'CaseConditionUndertakingController'
            => 'Olcs\Controller\Cases\ConditionUndertaking\ConditionUndertakingController',
            'CasePublicInquiryController' => 'Olcs\Controller\Cases\PublicInquiry\PublicInquiryController',
            'CaseNonPublicInquiryController' => 'Olcs\Controller\Cases\NonPublicInquiry\NonPublicInquiryController',
            'PublicInquiry\SlaController' => 'Olcs\Controller\Cases\PublicInquiry\SlaController',
            'PublicInquiry\HearingController' => 'Olcs\Controller\Cases\PublicInquiry\HearingController',
            'PublicInquiry\AgreedAndLegislationController'
            => 'Olcs\Controller\Cases\PublicInquiry\AgreedAndLegislationController',
            'PublicInquiry\RegisterDecisionController'
            => 'Olcs\Controller\Cases\PublicInquiry\RegisterDecisionController',
            'CaseProcessingController' => 'Olcs\Controller\Cases\Processing\ProcessingController',
            'CaseNoteController' => 'Olcs\Controller\Cases\Processing\NoteController',
            'CaseTaskController' => 'Olcs\Controller\Cases\Processing\TaskController',
            'CaseDecisionsController' => 'Olcs\Controller\Cases\Processing\DecisionsController',
            'CaseRevokeController' => 'Olcs\Controller\Cases\Processing\RevokeController',
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'IndexController' => 'Olcs\Controller\IndexController',
            'SearchController' => 'Olcs\Controller\SearchController',
            'DocumentController' => 'Olcs\Controller\Document\DocumentController',
            'DocumentGenerationController' => 'Olcs\Controller\Document\DocumentGenerationController',
            'DocumentUploadController' => 'Olcs\Controller\Document\DocumentUploadController',
            'DocumentFinaliseController' => 'Olcs\Controller\Document\DocumentFinaliseController',
            'LicenceController' => 'Olcs\Controller\Licence\LicenceController',
            'TaskController' => 'Olcs\Controller\TaskController',
            'LicenceDetailsOverviewController' => 'Olcs\Controller\Licence\Details\OverviewController',
            'LicenceDetailsTypeOfLicenceController' => 'Olcs\Controller\Licence\Details\TypeOfLicenceController',
            'LicenceDetailsBusinessDetailsController' => 'Olcs\Controller\Licence\Details\BusinessDetailsController',
            'LicenceDetailsAddressController' => 'Olcs\Controller\Licence\Details\AddressController',
            'LicenceDetailsPeopleController' => 'Olcs\Controller\Licence\Details\PeopleController',
            'LicenceDetailsOperatingCentreController' => 'Olcs\Controller\Licence\Details\OperatingCentreController',
            'LicenceDetailsTransportManagerController' => 'Olcs\Controller\Licence\Details\TransportManagerController',
            'LicenceDetailsVehicleController' => 'Olcs\Controller\Licence\Details\VehicleController',
            'LicenceDetailsVehiclePsvController' => 'Olcs\Controller\Licence\Details\VehiclePsvController',
            'LicenceDetailsDiscsPsvController' => 'Olcs\Controller\Licence\Details\DiscsPsvController',
            'LicenceDetailsSafetyController' => 'Olcs\Controller\Licence\Details\SafetyController',
            'LicenceDetailsConditionUndertakingController' =>
            'Olcs\Controller\Licence\Details\ConditionUndertakingController',
            'LicenceDetailsTaxiPhvController' => 'Olcs\Controller\Licence\Details\TaxiPhvController',
            'ApplicationController' => 'Olcs\Controller\Application\ApplicationController',
            'ApplicationProcessingTasksController'
                => 'Olcs\Controller\Application\Processing\ApplicationProcessingTasksController',
            'ApplicationProcessingOverviewController' =>
                'Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController',
            'ApplicationProcessingNoteController' =>
                'Olcs\Controller\Application\Processing\ApplicationProcessingNoteController',
            'LicenceProcessingOverviewController' =>
            'Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController',
            'LicenceProcessingPublicationsController' =>
             'Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsController',
            'LicenceProcessingTasksController' => 'Olcs\Controller\Licence\Processing\LicenceProcessingTasksController',
            'LicenceProcessingNoteController' => 'Olcs\Controller\Licence\Processing\LicenceProcessingNoteController',
            'BusController' => 'Olcs\Controller\Bus\BusController',
            'BusDetailsController' => 'Olcs\Controller\Bus\Details\BusDetailsController',
            'BusDetailsServiceController' => 'Olcs\Controller\Bus\Details\BusDetailsServiceController',
            'BusDetailsStopController' => 'Olcs\Controller\Bus\Details\BusDetailsStopController',
            'BusDetailsTaController' => 'Olcs\Controller\Bus\Details\BusDetailsTaController',
            'BusDetailsQualityController' => 'Olcs\Controller\Bus\Details\BusDetailsQualityController',
            'BusShortController' => 'Olcs\Controller\Bus\Short\BusShortController',
            'BusShortPlaceholderController' => 'Olcs\Controller\Bus\Short\BusShortPlaceholderController',
            'BusRouteController' => 'Olcs\Controller\Bus\Route\BusRouteController',
            'BusRoutePlaceholderController' => 'Olcs\Controller\Bus\Route\BusRoutePlaceholderController',
            'BusTrcController' => 'Olcs\Controller\Bus\Trc\BusTrcController',
            'BusTrcPlaceholderController' => 'Olcs\Controller\Bus\Trc\BusTrcPlaceholderController',
            'BusDocsController' => 'Olcs\Controller\Bus\Docs\BusDocsController',
            'BusDocsPlaceholderController' => 'Olcs\Controller\Bus\Docs\BusDocsPlaceholderController',
            'BusProcessingController' => 'Olcs\Controller\Bus\Processing\BusProcessingController',
            'BusProcessingDecisionController' => 'Olcs\Controller\Bus\Processing\BusProcessingDecisionController',
            'BusProcessingNoteController' => 'Olcs\Controller\Bus\Processing\BusProcessingNoteController',
            'BusProcessingRegistrationHistoryController' =>
                'Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController',
            'BusProcessingTaskController' => 'Olcs\Controller\Bus\Processing\BusProcessingTaskController',
            'BusFeesController' => 'Olcs\Controller\Bus\Fees\BusFeesController',
            'BusFeesPlaceholderController' => 'Olcs\Controller\Bus\Fees\BusFeesPlaceholderController',
            'BusServiceController' => 'Olcs\Controller\Bus\Service\BusServiceController',
            'OperatorController' => 'Olcs\Controller\Operator\OperatorController',
            'OperatorBusinessDetailsController' => 'Olcs\Controller\Operator\OperatorBusinessDetailsController',
            'OperatorPeopleController' => 'Olcs\Controller\Operator\OperatorPeopleController',
            'OperatorLicencesApplicationsController' =>
                'Olcs\Controller\Operator\OperatorLicencesApplicationsController',
            'TMController' => 'Olcs\Controller\TransportManager\TransportManagerController',
            'TMDetailsDetailController' =>
                'Olcs\Controller\TransportManager\Details\TransportManagerDetailsDetailController',
            'TMDetailsCompetenceController' =>
                'Olcs\Controller\TransportManager\Details\TransportManagerDetailsCompetenceController',
            'TMDetailsResponsibilityController' =>
                'Olcs\Controller\TransportManager\Details\TransportManagerDetailsResponsibilityController',
            'TMDetailsEmploymentController' =>
                'Olcs\Controller\TransportManager\Details\TransportManagerDetailsEmploymentController',
            'TMDetailsPreviousHistoryController' =>
                'Olcs\Controller\TransportManager\Details\TransportManagerDetailsPreviousHistoryController',
            'TMProcessingDecisionController' =>
                'Olcs\Controller\TransportManager\Processing\TransportManagerProcessingDecisionController',
            'TMProcessingHistoryController' =>
                'Olcs\Controller\TransportManager\Processing\TransportManagerProcessingHistoryController',
            'TMProcessingPublicationController' =>
                'Olcs\Controller\TransportManager\Processing\PublicationController',
            'TMProcessingNoteController' =>
                'Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteController',
            'TMProcessingTaskController' =>
                'Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController',
            'TMCaseController' =>
                'Olcs\Controller\TransportManager\TransportManagerCaseController',
            'TMDocumentController' => 'Olcs\Controller\TransportManager\TransportManagerDocumentController'
        )
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'Olcs\Mvc\Controller\Plugin\Confirm' => 'Olcs\Mvc\Controller\Plugin\Confirm'
        ),
        'aliases' => array(
            'confirm' => 'Olcs\Mvc\Controller\Plugin\Confirm'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'pages/404',
        'exception_template' => 'pages/500',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/base.phtml',
            'pages/404' => __DIR__ . '/../view/pages/404.phtml',
            'pages/500' => __DIR__ . '/../view/pages/500.phtml'
        ),
        'template_path_stack' => array(
            'olcs' => dirname(__DIR__) . '/view',
            //'olcs/view' => dirname(__DIR__) . '/view',
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'addressFormat' => 'Olcs\View\Helper\Address',
            'pageTitle' => 'Olcs\View\Helper\PageTitle',
            'pageSubtitle' => 'Olcs\View\Helper\PageSubtitle',
            'tableFilters' => 'Olcs\View\Helper\TableFilters',
            'piListData' => 'Olcs\View\Helper\PiListData',
            'formSubmissionSections' => 'Olcs\Form\View\Helper\SubmissionSections',
            'submissionSectionDetails' => 'Olcs\View\Helper\SubmissionSectionDetails',
            'submissionSectionOverview' => 'Olcs\View\Helper\SubmissionSectionOverview',
            'markers' => 'Olcs\View\Helper\Markers',
        ),
        'delegators' => array(
            'formElement' => array('Olcs\Form\View\Helper\FormElementDelegatorFactory')
        ),
        'factories' => array(
            'SubmissionSectionTable' => 'Olcs\View\Helper\SubmissionSectionTableFactory',
            'SubmissionSectionMultipleTables' => 'Olcs\View\Helper\SubmissionSectionMultipleTablesFactory',
            'Olcs\View\Helper\SlaIndicator' => 'Olcs\View\Helper\SlaIndicator'
        ),
        'aliases' => [
            'slaIndicator' => 'Olcs\View\Helper\SlaIndicator'
        ]
    ),
    'local_forms_path' => array(
        __DIR__ . '/../src/Form/Forms/'
    ),
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        ),
        'right-sidebar' => array(
            include __DIR__ . '/navigation-right-sidebar.config.php'
        )
    ),
    //-------- End navigation -----------------
    'submission_config' => include __DIR__ . '/submission/submission.config.php',
    'local_scripts_path' => array(
        __DIR__ . '/../assets/js/inline/'
    ),
    'asset_path' => '//dvsa-static.olcsdv-ap01.olcs.npm',
    'service_manager' => array(
        'aliases' => [
            'NavigationFactory' => 'Olcs\Service\NavigationFactory',
            'RouteParamsListener' => 'Olcs\Listener\RouteParams',
            'right-sidebar' => 'Olcs\Navigation\RightHandNavigation',
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service',
            'HeaderSearchListener' => 'Olcs\Listener\HeaderSearch'
        ],
        'invokables' => [
            'VariationUtility' => 'Olcs\Service\Utility\VariationUtility',
            'ApplicationUtility' => 'Olcs\Service\Utility\ApplicationUtility',
            'VariationOperatingCentreAdapter'
                => 'Olcs\Controller\Lva\Adapters\VariationOperatingCentreAdapter',
            'Olcs\Service\Marker\MarkerPluginManager' => 'Olcs\Service\Marker\MarkerPluginManager',
            'Olcs\Service\NavigationFactory' => 'Olcs\Service\NavigationFactory',
            'Olcs\Listener\RouteParams' => 'Olcs\Listener\RouteParams',
            'Olcs\Service\Data\Mapper\Opposition' => 'Olcs\Service\Data\Mapper\Opposition',
        ],
        'factories' => array(
            'Olcs\Listener\RouteParam\BusRegId' => 'Olcs\Listener\RouteParam\BusRegId',
            'Olcs\Listener\RouteParam\BusRegAction' => 'Olcs\Listener\RouteParam\BusRegAction',
            'Olcs\Listener\RouteParam\BusRegMarker' => 'Olcs\Listener\RouteParam\BusRegMarker',
            'Olcs\Listener\RouteParam\Action' => 'Olcs\Listener\RouteParam\Action',
            'Olcs\Listener\RouteParam\TransportManager' => 'Olcs\Listener\RouteParam\TransportManager',
            'Olcs\Listener\RouteParam\Application' => 'Olcs\Listener\RouteParam\Application',
            'Olcs\Listener\RouteParam\Cases' => 'Olcs\Listener\RouteParam\Cases',
            'Olcs\Listener\RouteParam\Licence' => 'Olcs\Listener\RouteParam\Licence',
            'Olcs\Listener\RouteParam\Marker' => 'Olcs\Listener\RouteParam\Marker',
            'Olcs\Listener\RouteParam\LicenceTitle' => 'Olcs\Listener\RouteParam\LicenceTitle',
            'Olcs\Service\Data\BusNoticePeriod' => 'Olcs\Service\Data\BusNoticePeriod',
            'Olcs\Service\Data\BusServiceType' => 'Olcs\Service\Data\BusServiceType',
            'Olcs\Service\Data\User' => 'Olcs\Service\Data\User',
            'Olcs\Service\Data\Team' => 'Olcs\Service\Data\Team',
            'Olcs\Service\Data\PresidingTc' => 'Olcs\Service\Data\PresidingTc',
            'Olcs\Service\Data\SiPenaltyType' => 'Olcs\Service\Data\SiPenaltyType',
            'Olcs\Service\Data\Submission' => 'Olcs\Service\Data\Submission',
            'Olcs\Service\Data\SubmissionSectionComment' => 'Olcs\Service\Data\SubmissionSectionComment',
            'Olcs\Service\Data\Fee' => 'Olcs\Service\Data\Fee',
            'Olcs\Service\Data\Cases' => 'Olcs\Service\Data\Cases',
            'Olcs\Service\Data\Search\SearchTypeManager' => 'Olcs\Service\Data\Search\SearchTypeManagerFactory',
            'Olcs\Service\Data\Pi' => 'Olcs\Service\Data\Pi',
            'Olcs\Service\Data\TaskSubCategory' => 'Olcs\Service\Data\TaskSubCategory',
            'Olcs\Service\Data\ApplicationOperatingCentre' => 'Olcs\Service\Data\ApplicationOperatingCentre',
            'Olcs\Navigation\RightHandNavigation' => 'Olcs\Navigation\RightHandNavigationFactory',
            'Olcs\Service\Utility\DateUtility' => 'Olcs\Service\Utility\DateUtilityFactory',
            'Olcs\Listener\HeaderSearch' => 'Olcs\Listener\HeaderSearch',
            'Olcs\Service\Utility\PublicationHelper' => 'Olcs\Service\Utility\PublicationHelperFactory',
        )
    ),
    'form_elements' => [
        'factories' => [
            'PublicInquiryReason' => 'Olcs\Form\Element\PublicInquiryReasonFactory',
            'SubmissionSections' => 'Olcs\Form\Element\SubmissionSectionsFactory',
            'Olcs\Form\Element\SlaDateSelect' => 'Olcs\Form\Element\SlaDateSelectFactory',
            'Olcs\Form\Element\SlaDateTimeSelect' => 'Olcs\Form\Element\SlaDateTimeSelectFactory'
        ],
        'aliases' => [
            'SlaDateSelect' => 'Olcs\Form\Element\SlaDateSelect',
            'SlaDateTimeSelect' => 'Olcs\Form\Element\SlaDateTimeSelect'
        ]
    ],
    'search' => [
        'invokables' => [
            'licence' => 'Olcs\Data\Object\Search\Licence',
            'application' => 'Olcs\Data\Object\Search\Application',
            'case' => 'Olcs\Data\Object\Search\Cases',
            'psv_disc' => 'Olcs\Data\Object\Search\PsvDisc',
            'vehicle' => 'Olcs\Data\Object\Search\Vehicle',
            'address' => 'Olcs\Data\Object\Search\Address',
            'bus_reg' => 'Olcs\Data\Object\Search\BusReg',
            'people' => 'Olcs\Data\Object\Search\People',
        ]
    ],
    'route_param_listeners' => [
        'Olcs\Controller\Interfaces\CaseControllerInterface' => [
            'Olcs\Listener\RouteParam\Cases',
            'Olcs\Listener\RouteParam\Licence',
            'Olcs\Listener\RouteParam\LicenceTitle',
            'Olcs\Listener\RouteParam\Marker',
            'Olcs\Listener\RouteParam\Application',
            'Olcs\Listener\RouteParam\TransportManager',
            'Olcs\Listener\RouteParam\Action',
            'Olcs\Listener\HeaderSearch'
        ],
        'Olcs\Controller\Interfaces\ApplicationControllerInterface' => [
            'Olcs\Listener\RouteParam\Cases',
            'Olcs\Listener\RouteParam\Licence',
            'Olcs\Listener\RouteParam\LicenceTitle',
            'Olcs\Listener\RouteParam\Marker',
            'Olcs\Listener\RouteParam\Application',
            'Olcs\Listener\RouteParam\TransportManager',
            'Olcs\Listener\RouteParam\Action',
            'Olcs\Listener\HeaderSearch'
        ],
        'Olcs\Controller\Interfaces\BusRegControllerInterface' => [
            'Olcs\Listener\RouteParam\Marker',
            'Olcs\Listener\RouteParam\Application',
            'Olcs\Listener\RouteParam\BusRegId',
            'Olcs\Listener\RouteParam\BusRegAction',
            'Olcs\Listener\RouteParam\BusRegMarker',
            'Olcs\Listener\RouteParam\Licence',
            'Olcs\Listener\HeaderSearch'
        ],
        'Olcs\Controller\Interfaces\TransportManagerControllerInterface' => [
            'Olcs\Listener\RouteParam\TransportManager',
            'Olcs\Listener\RouteParam\Application',
            'Olcs\Listener\HeaderSearch'
        ],
        'Olcs\Controller\Interfaces\LicenceControllerInterface' => [
            'Olcs\Listener\RouteParam\Licence',
            'Olcs\Listener\RouteParam\LicenceTitle',
            'Olcs\Listener\HeaderSearch'
        ]
    ],
    'data_services' => [
        'factories' => [
            'Olcs\Service\Data\SubmissionLegislation' => 'Olcs\Service\Data\SubmissionLegislation',
            'Olcs\Service\Data\PublicInquiryReason' => 'Olcs\Service\Data\PublicInquiryReason',
            'Olcs\Service\Data\PublicInquiryDecision' => 'Olcs\Service\Data\PublicInquiryDecision',
            'Olcs\Service\Data\PublicInquiryDefinition' => 'Olcs\Service\Data\PublicInquiryDefinition',
            'Olcs\Service\Data\ImpoundingLegislation' => 'Olcs\Service\Data\ImpoundingLegislation',
        ]
    ],
    'filters' => [
        'invokables' => [
            'Olcs\Filter\SubmissionSection\ComplianceComplaints' =>
                'Olcs\Filter\SubmissionSection\ComplianceComplaints',
            'Olcs\Filter\SubmissionSection\ConditionsAndUndertakings' =>
                'Olcs\Filter\SubmissionSection\ConditionsAndUndertakings',
            'Olcs\Filter\SubmissionSection\ConvictionFpnOffenceHistory' =>
                'Olcs\Filter\SubmissionSection\ConvictionFpnOffenceHistory',
            'Olcs\Filter\SubmissionSection\CaseSummary' => 'Olcs\Filter\SubmissionSection\CaseSummary',
            'Olcs\Filter\SubmissionSection\CaseOutline' => 'Olcs\Filter\SubmissionSection\CaseOutline',
            'Olcs\Filter\SubmissionSection\Persons' => 'Olcs\Filter\SubmissionSection\Persons',
            'Olcs\Filter\SubmissionSection\Oppositions' => 'Olcs\Filter\SubmissionSection\Oppositions',
            'Olcs\Filter\SubmissionSection\LinkedLicencesAppNumbers' =>
                'Olcs\Filter\SubmissionSection\LinkedLicencesAppNumbers',
            'Olcs\Filter\SubmissionSection\LeadTcArea' => 'Olcs\Filter\SubmissionSection\LeadTcArea',
            'Olcs\Filter\SubmissionSection\ProhibitionHistory' => 'Olcs\Filter\SubmissionSection\ProhibitionHistory',
            'Olcs\Filter\SubmissionSection\Penalties' => 'Olcs\Filter\SubmissionSection\Penalties',
            'Olcs\Filter\SubmissionSection\AnnualTestHistory' => 'Olcs\Filter\SubmissionSection\AnnualTestHistory',
            'Olcs\Filter\SubmissionSection\AuthRequestedAppliedFor' =>
                'Olcs\Filter\SubmissionSection\AuthRequestedAppliedFor',
            'Olcs\Filter\SubmissionSection\EnvironmentalComplaints' =>
                'Olcs\Filter\SubmissionSection\EnvironmentalComplaints',
            'Olcs\Filter\SubmissionSection\OutstandingApplications' =>
                'Olcs\Filter\SubmissionSection\OutstandingApplications',
            'Olcs\Filter\SubmissionSection\Statements' => 'Olcs\Filter\SubmissionSection\Statements',
            'Olcs\Filter\SubmissionSection\TransportManagers' => 'Olcs\Filter\SubmissionSection\TransportManagers',
            'Olcs\Filter\SubmissionSection\OperatingCentres' => 'Olcs\Filter\SubmissionSection\OperatingCentres',
            'Olcs\Filter\SubmissionSection\MostSeriousInfringement' =>
                'Olcs\Filter\SubmissionSection\MostSeriousInfringement',
            'Olcs\Filter\SubmissionSection\TmDetails' => 'Olcs\Filter\SubmissionSection\TmDetails',
            'Olcs\Filter\SubmissionSection\TmQualifications' => 'Olcs\Filter\SubmissionSection\TmQualifications',
            'Olcs\Filter\SubmissionSection\TmResponsibilities' => 'Olcs\Filter\SubmissionSection\TmResponsibilities',
            'Olcs\Filter\SubmissionSection\TmOtherEmployment' => 'Olcs\Filter\SubmissionSection\TmOtherEmployment',
            'Olcs\Filter\SubmissionSection\TmPreviousHistory' => 'Olcs\Filter\SubmissionSection\TmPreviousHistory'

        ],
        'aliases' => [
            'ComplianceComplaints' => 'Olcs\Filter\SubmissionSection\ComplianceComplaints',
            'ConditionsAndUndertakings' => 'Olcs\Filter\SubmissionSection\ConditionsAndUndertakings',
            'ConvictionFpnOffenceHistory' => 'Olcs\Filter\SubmissionSection\ConvictionFpnOffenceHistory',
            'CaseSummary' => 'Olcs\Filter\SubmissionSection\CaseSummary',
            'CaseOutline' => 'Olcs\Filter\SubmissionSection\CaseOutline',
            'Persons' => 'Olcs\Filter\SubmissionSection\Persons',
            'Oppositions' => 'Olcs\Filter\SubmissionSection\Oppositions',
            'LinkedLicencesAppNumbers' => 'Olcs\Filter\SubmissionSection\LinkedLicencesAppNumbers',
            'LeadTcArea' => 'Olcs\Filter\SubmissionSection\LeadTcArea',
            'ProhibitionHistory' => 'Olcs\Filter\SubmissionSection\ProhibitionHistory',
            'Penalties' => 'Olcs\Filter\SubmissionSection\Penalties',
            'AnnualTestHistory' => 'Olcs\Filter\SubmissionSection\AnnualTestHistory',
            'AuthRequestedAppliedFor' => 'Olcs\Filter\SubmissionSection\AuthRequestedAppliedFor',
            'EnvironmentalComplaints' => 'Olcs\Filter\SubmissionSection\EnvironmentalComplaints',
            'Statements' => 'Olcs\Filter\SubmissionSection\Statements',
            'TransportManagers' => 'Olcs\Filter\SubmissionSection\TransportManagers',
            'OperatingCentres' => 'Olcs\Filter\SubmissionSection\OperatingCentres',
            'MostSeriousInfringement' => 'Olcs\Filter\SubmissionSection\MostSeriousInfringement',
            'TmDetails' => 'Olcs\Filter\SubmissionSection\TmDetails',
            'TmQualifications' => 'Olcs\Filter\SubmissionSection\TmQualifications',
            'TmResponsibilities' => 'Olcs\Filter\SubmissionSection\TmResponsibilities',
            'TmOtherEmployment' => 'Olcs\Filter\SubmissionSection\TmOtherEmployment',
            'TmPreviousHistory' => 'Olcs\Filter\SubmissionSection\TmPreviousHistory',
        ]
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' =>[
                'zfcuser/login'    => ['*'],
                'zfcuser/logout'    => ['*'],
                'case_processing_notes' => ['note'],
                '*case*' => ['case'],
                '*documents*' => ['documents'],
                'note' => ['note'],
                '*' => ['view']
            ]
        ]
    ]
);
