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
        'invokables' => array(
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'IndexController' => 'Olcs\Controller\IndexController',
            'SearchController' => 'Olcs\Controller\SearchController',
            'CaseController' => 'Olcs\Controller\Cases\CaseController',
            'CaseStatementController' => 'Olcs\Controller\Cases\Statement\StatementController',
            'CaseAppealController' => 'Olcs\Controller\CaseAppealController',
            'CaseComplaintController' => 'Olcs\Controller\CaseComplaintController',
            'CaseConvictionController' => 'Olcs\Controller\Cases\Conviction\ConvictionController',
            'CaseSubmissionController' => 'Olcs\Controller\Cases\Submission\SubmissionController',
            'SubmissionController' => 'Olcs\Controller\Submission\SubmissionController',
            'CaseStayController' => 'Olcs\Controller\CaseStayController',
            'CasePenaltyController' => 'Olcs\Controller\CasePenaltyController',
            'CaseProhibitionController' => 'Olcs\Controller\Cases\Prohibition\ProhibitionController',
            'CaseProhibitionDefectController' => 'Olcs\Controller\CaseProhibitionDefectController',
            'CaseAnnualTestHistoryController' => 'Olcs\Controller\CaseAnnualTestHistoryController',
            'SubmissionNoteController' => 'Olcs\Controller\Submission\SubmissionNoteController',
            'CaseImpoundingController' => 'Olcs\Controller\CaseImpoundingController',
            'CaseConditionUndertakingController' => 'Olcs\Controller\CaseConditionUndertakingController',
            'CaseRevokeController' => 'Olcs\Controller\CaseRevokeController',
            'CasePiController' => 'Olcs\Controller\CasePiController',
            'CasePiHearingController' => 'Olcs\Controller\Cases\Pi\HearingController',
            'DocumentController' => 'Olcs\Controller\DocumentController',
            'DefendantSearchController' => 'Olcs\DefendantSearchController',
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
            'LicenceDetailsSafetyController' => 'Olcs\Controller\Licence\Details\SafetyController',
            'LicenceDetailsConditionUndertakingController' =>
                'Olcs\Controller\Licence\Details\ConditionUndertakingController',
            'LicenceDetailsTaxiPhvController' => 'Olcs\Controller\Licence\Details\TaxiPhvController',
            'ApplicationController' => 'Olcs\Controller\Application\ApplicationController',
            'LicenceProcessingOverviewController' =>
                'Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController',
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
            'BusProcessingNoteController' => 'Olcs\Controller\Bus\Processing\BusProcessingNoteController',
            'BusFeesController' => 'Olcs\Controller\Bus\Fees\BusFeesController',
            'BusFeesPlaceholderController' => 'Olcs\Controller\Bus\Fees\BusFeesPlaceholderController',
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/base.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml'
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
            'pageTitle'    => 'Olcs\View\Helper\PageTitle',
            'pageSubtitle' => 'Olcs\View\Helper\PageSubtitle',
            'tableFilters' => 'Olcs\View\Helper\TableFilters',
            'formSubmissionSections' => 'Olcs\Form\View\Helper\SubmissionSections',
            'formSubmissionSectionsMultiCheckbox' => 'Olcs\Form\View\Helper\SubmissionSectionsMultiCheckbox'
        ),
        'delegators' => array(
            'formElement' => array('Olcs\Form\View\Helper\FormElementDelegatorFactory')
        )
    ),
    'local_forms_path' => array(
        __DIR__ . '/../src/Form/Forms/'
    ),
    //-------- Start navigation -----------------
    'navigation' => array(
        'default' => array(
            include __DIR__ . '/navigation.config.php'
        )
    ),
    //-------- End navigation -----------------
    'submission_config' => include __DIR__ . '/submission/submission.config.php',
    'local_scripts_path' => array(
        __DIR__ . '/../assets/js/inline/'
    ),
    'asset_path' => '//dvsa-static.olcsdv-ap01.olcs.npm',
    'service_manager' => array(
        'factories' => array(
            'ApplicationJourneyHelper' => function ($sm) {
                $helper = new \Olcs\Helper\ApplicationJourneyHelper();
                $helper->setServiceLocator($sm);
                return $helper;
            },
            'Olcs\Service\Data\PublicInquiryReason' => 'Olcs\Service\Data\PublicInquiryReason',
            'Olcs\Service\Data\Licence' => 'Olcs\Service\Data\Licence',
            'Olcs\Service\Data\User' => 'Olcs\Service\Data\User',
            'Olcs\Service\Data\PresidingTc' => 'Olcs\Service\Data\PresidingTc'
        )
    ),
    'application_journey' => array(
        'access_keys' => array(
            'internal'
        ),
        'templates' => array(
            'not-found' => 'journey/not-found',
            'navigation' => 'journey/application/navigation',
            'main' => 'journey/application/main',
            'layout' => 'journey/application/layout'
        ),
        'render' => array(
            'pre-render' => array(
                'service' => 'ApplicationJourneyHelper',
                'method' => 'render'
            )
        )
    ),
    'form_elements' =>[
        'factories' => [
            'PublicInquiryReason' => 'Olcs\Form\Element\PublicInquiryReasonFactory',
            'SubmissionSections' => 'Olcs\Form\Element\SubmissionSectionsFactory'
        ]
    ]

);
