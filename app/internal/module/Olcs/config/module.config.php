<?php

use Common\Controller\Lva\ReviewController;
use Common\Data\Object\Search\Address;
use Common\Data\Object\Search\Application;
use Common\Data\Object\Search\BusReg;
use Common\Data\Object\Search\IrfoOrganisation;
use Common\Data\Object\Search\Licence as LicenceSearch;
use Common\Data\Object\Search\People;
use Common\Data\Object\Search\PsvDisc;
use Common\Data\Object\Search\Publication;
use Common\Data\Object\Search\User;
use Common\Data\Object\Search\Vehicle;
use Common\Form\Elements\Validators\TableRequiredValidator;
use Common\Service\Data as CommonDataService;
use Laminas\Cache\Service\StorageCacheAbstractServiceFactory;
use Olcs\Auth;
use Olcs\Controller\Application as ApplicationControllers;
use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Application\Processing\ApplicationProcessingInspectionRequestController;
use Olcs\Controller\Application\Processing\ApplicationProcessingInspectionRequestControllerFactory;
use Olcs\Controller\Application\Processing\ApplicationProcessingPublicationsController;
use Olcs\Controller\Application\Processing\ApplicationProcessingPublicationsControllerFactory;
use Olcs\Controller\Bus as BusControllers;
use Olcs\Controller\Bus\Registration\BusRegistrationController;
use Olcs\Controller\Bus\Registration\BusRegistrationControllerFactory;
use Olcs\Controller\Bus\Service\BusServiceController;
use Olcs\Controller\Bus\Service\BusServiceControllerFactory;
use Olcs\Controller\Cases;
use Olcs\Controller\Cases as CaseControllers;
use Olcs\Controller\Cases\Overview\OverviewControllerFactory;
use Olcs\Controller\Document as DocumentControllers;
use Olcs\Controller\Factory\Application as ApplicationControllerFactories;
use Olcs\Controller\Factory\Bus as BusControllerFactories;
use Olcs\Controller\Factory\Cases as CaseControllerFactories;
use Olcs\Controller\Factory\DisqualifyControllerFactory;
use Olcs\Controller\Factory\Document as DocumentControllerFactories;
use Olcs\Controller\Factory\IrhpPermits as IrhpPermitsControllerFactories;
use Olcs\Controller\Factory\Licence as LicenceControllerFactories;
use Olcs\Controller\Factory\Operator as OperatorControllerFactories;
use Olcs\Controller\Factory\Operator\OperatorBusinessDetailsControllerFactory;
use Olcs\Controller\Factory\SearchControllerFactory;
use Olcs\Controller\Factory\SplitScreenControllerFactory;
use Olcs\Controller\Factory\TaskControllerFactory;
use Olcs\Controller\Factory\TransportManager\Details\TransportManagerDetailsPreviousHistoryControllerFactory;
use Olcs\Controller\Factory\TransportManager\Processing\TransportManagerProcessingTaskControllerFactory;
use Olcs\Controller\Factory\TransportManager\TransportManagerDocumentControllerFactory;
use Olcs\Controller\Factory\Variation\VariationSchedule41ControllerFactory;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Controller\Interfaces\SubmissionControllerInterface;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\IrhpPermits as IrhpPermitsControllers;
use Olcs\Controller\Licence as LicenceControllers;
use Olcs\Controller\Licence\Processing\LicenceProcessingInspectionRequestController;
use Olcs\Controller\Licence\Processing\LicenceProcessingInspectionRequestControllerFactory;
use Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController;
use Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsController;
use Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsControllerFactory;
use Olcs\Controller\Lva\Application as LvaApplicationControllers;
use Olcs\Controller\Lva\Factory\Controller\Application as LvaApplicationControllerFactories;
use Olcs\Controller\Lva\Factory\Controller\Licence as LvaLicenceControllerFactories;
use Olcs\Controller\Lva\Factory\Controller\Variation as LvaVariationControllerFactories;
use Olcs\Controller\Lva\Licence as LvaLicenceControllers;
use Olcs\Controller\Lva\Variation as LvaVariationControllers;
use Olcs\Controller\Operator as OperatorControllers;
use Olcs\Controller\Operator\HistoryController;
use Olcs\Controller\Operator\OperatorBusinessDetailsController;
use Olcs\Controller\SearchController;
use Olcs\Controller\Sla\CaseDocumentSlaTargetDateController;
use Olcs\Controller\Sla\LicenceDocumentSlaTargetDateController;
use Olcs\Controller\TaskController;
use Olcs\Controller\TransportManager as TmCntr;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsDetailController;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsDetailControllerFactory;
use Olcs\Controller\TransportManager\Details\TransportManagerDetailsPreviousHistoryController;
use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController;
use Olcs\Controller\TransportManager\TransportManagerController;
use Olcs\Controller\TransportManager\TransportManagerDocumentController;
use Olcs\Controller\Variation\VariationSchedule41Controller;
use Olcs\Form\Element\SearchDateRangeFieldsetFactory;
use Olcs\Form\Element\SearchFilterFieldsetFactory;
use Olcs\Form\Element\SubmissionSections;
use Olcs\Form\Element\SubmissionSectionsFactory;
use Olcs\FormService\Form\Lva\AbstractLvaFormFactory;
use Olcs\Listener\HeaderSearch;
use Olcs\Listener\RouteParam;
use Olcs\Listener\RouteParam\Application as ApplicationListener;
use Olcs\Listener\RouteParam\ApplicationFurniture;
use Olcs\Listener\RouteParam\BusRegFurniture;
use Olcs\Listener\RouteParam\CasesFurniture;
use Olcs\Listener\RouteParam\IrhpApplicationFurniture;
use Olcs\Listener\RouteParam\Licence as LicenceListener;
use Olcs\Listener\RouteParam\LicenceFurniture;
use Olcs\Listener\RouteParam\OrganisationFurniture;
use Olcs\Listener\RouteParam\SubmissionsFurniture;
use Olcs\Listener\RouteParam\TransportManagerFurniture;
use Olcs\Listener\RouteParam\VariationFurniture;
use Olcs\Mvc\Controller\Plugin\Placeholder;
use Olcs\Mvc\Controller\Plugin\PlaceholderFactory;
use Olcs\Mvc\Controller\Plugin\Script;
use Olcs\Mvc\Controller\Plugin\ScriptFactory;
use Olcs\Mvc\Controller\Plugin\Table;
use Olcs\Mvc\Controller\Plugin\TableFactory;
use Olcs\Mvc\Controller\Plugin\ViewBuilder;
use Olcs\Service\Data as DataService;
use Olcs\Service\Helper as HelperService;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationService;
use Olcs\Service\Helper\WebDavJsonWebTokenGenerationServiceFactory;
use Olcs\Service\Marker;
use Olcs\Service\Marker\MarkerPluginManager;
use Olcs\Service\Marker\MarkerPluginManagerFactory;
use Olcs\Service\Marker\MarkerService;
use Olcs\Service\Processing as ProcessingService;
use Olcs\View\Helper\SlaIndicator;
use Olcs\View\Helper\SubmissionSectionMultipleTablesFactory;
use Olcs\View\Helper\SubmissionSectionTableFactory;
use Olcs\Controller\TransportManager as TransportManagerControllers;
use Olcs\Controller\Factory\TransportManager as TransportManagerControllerFactories;

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
        'delegators' => array(
            'LvaApplication/ConditionsUndertakings' => array(
                'Common\Controller\Lva\Delegators\ApplicationConditionsUndertakingsDelegator'
            ),
            'LvaVariation/ConditionsUndertakings' => array(
                'Common\Controller\Lva\Delegators\VariationConditionsUndertakingsDelegator'
            ),
            'LvaLicence/ConditionsUndertakings' => array(
                'Common\Controller\Lva\Delegators\LicenceConditionsUndertakingsDelegator'
            ),
        ),
        'lva_controllers' => array(
            'LvaApplication' => Olcs\Controller\Lva\Application\OverviewController::class,
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
            'LvaApplication/Review' => ReviewController::class,
            'LvaApplication/Grant' => 'Olcs\Controller\Lva\Application\GrantController',
            'LvaApplication/Withdraw' => 'Olcs\Controller\Lva\Application\WithdrawController',
            'LvaApplication/Refuse' => 'Olcs\Controller\Lva\Application\RefuseController',
            'LvaApplication/NotTakenUp' => 'Olcs\Controller\Lva\Application\NotTakenUpController',
            'LvaApplication/ReviveApplication' => 'Olcs\Controller\Lva\Application\ReviveApplicationController',
            'LvaApplication/DeclarationsInternal' => 'Olcs\Controller\Lva\Application\DeclarationsInternalController',
            'LvaApplication/Publish' => 'Olcs\Controller\Lva\Application\PublishController',
            'LvaApplication/Submit' => Olcs\Controller\Lva\Application\SubmitController::class,
            'ApplicationSchedule41Controller' => 'Olcs\Controller\Application\ApplicationSchedule41Controller',
            'VariationSchedule41Controller' => 'Olcs\Controller\Variation\VariationSchedule41Controller',
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
            'LvaLicence/Variation' => 'Olcs\Controller\Lva\Licence\VariationController',
            'LvaLicence/Trailers' => 'Olcs\Controller\Lva\Licence\TrailersController',
            'LvaVariation' => Olcs\Controller\Lva\Variation\OverviewController::class,
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
            'LvaVariation/LicenceHistory' => 'Olcs\Controller\Lva\Variation\LicenceHistoryController',
            'LvaVariation/ConvictionsPenalties' => 'Olcs\Controller\Lva\Variation\ConvictionsPenaltiesController',
            'LvaVariation/VehiclesDeclarations' => 'Olcs\Controller\Lva\Variation\VehiclesDeclarationsController',
            'LvaVariation/Review' => ReviewController::class,
            'LvaVariation/Grant' => 'Olcs\Controller\Lva\Variation\GrantController',
            'LvaVariation/Withdraw' => 'Olcs\Controller\Lva\Variation\WithdrawController',
            'LvaVariation/Refuse' => 'Olcs\Controller\Lva\Variation\RefuseController',
            'LvaVariation/Submit' => Olcs\Controller\Lva\Variation\SubmitController::class,
            'LvaVariation/Revive' => 'Olcs\Controller\Lva\Variation\ReviveApplicationController',
            'LvaVariation/DeclarationsInternal' => 'Olcs\Controller\Lva\Variation\DeclarationsInternalController',
            'LvaVariation/Publish' => 'Olcs\Controller\Lva\Variation\PublishController',
        ),
        'factories' => [
            TmCntr\Details\TransportManagerDetailsResponsibilityController::class => TransportManagerControllerFactories\Details\TransportManagerDetailsResponsibilityControllerFactory::class,
            \Olcs\Controller\Auth\LoginController::class => \Olcs\Controller\Auth\LoginControllerFactory::class,
            LvaApplicationControllers\AddressesController::class => LvaApplicationControllerFactories\AddressesControllerFactory::class,
            LvaApplicationControllers\BusinessDetailsController::class => LvaApplicationControllerFactories\BusinessDetailsControllerFactory::class,
            LvaApplicationControllers\BusinessTypeController::class => LvaApplicationControllerFactories\BusinessTypeControllerFactory::class,
            LvaApplicationControllers\CommunityLicencesController::class => LvaApplicationControllerFactories\CommunityLicencesControllerFactory::class,
            LvaApplicationControllers\ConditionsUndertakingsController::class => LvaApplicationControllerFactories\ConditionsUndertakingsControllerFactory::class,
            LvaApplicationControllers\ConvictionsPenaltiesController::class => LvaApplicationControllerFactories\ConvictionsPenaltiesControllerFactory::class,
            LvaApplicationControllers\DeclarationsInternalController::class => LvaApplicationControllerFactories\DeclarationsInternalControllerFactory::class,
            LvaApplicationControllers\FinancialEvidenceController::class => LvaApplicationControllerFactories\FinancialEvidenceControllerFactory::class,
            LvaApplicationControllers\FinancialHistoryController::class => LvaApplicationControllerFactories\FinancialHistoryControllerFactory::class,
            LvaApplicationControllers\GrantController::class => LvaApplicationControllerFactories\GrantControllerFactory::class,
            LvaApplicationControllers\InterimController::class => LvaApplicationControllerFactories\InterimControllerFactory::class,
            LvaApplicationControllers\LicenceHistoryController::class => LvaApplicationControllerFactories\LicenceHistoryControllerFactory::class,
            LvaApplicationControllers\NotTakenUpController::class => LvaApplicationControllerFactories\NotTakenUpControllerFactory::class,
            LvaApplicationControllers\OperatingCentresController::class => LvaApplicationControllerFactories\OperatingCentresControllerFactory::class,
            LvaApplicationControllers\OverviewController::class => LvaApplicationControllerFactories\OverviewControllerFactory::class,
            LvaApplicationControllers\PeopleController::class => LvaApplicationControllerFactories\PeopleControllerFactory::class,
            LvaApplicationControllers\PublishController::class => LvaApplicationControllerFactories\PublishControllerFactory::class,
            LvaApplicationControllers\RefuseController::class => LvaApplicationControllerFactories\RefuseControllerFactory::class,
            LvaApplicationControllers\ReviveApplicationController::class => LvaApplicationControllerFactories\ReviveApplicationControllerFactory::class,
            LvaApplicationControllers\SafetyController::class => LvaApplicationControllerFactories\SafetyControllerFactory::class,
            LvaApplicationControllers\SubmitController::class => LvaApplicationControllerFactories\SubmitControllerFactory::class,
            LvaApplicationControllers\TaxiPhvController::class => LvaApplicationControllerFactories\TaxiPhvControllerFactory::class,
            LvaApplicationControllers\TransportManagersController::class => LvaApplicationControllerFactories\TransportManagersControllerFactory::class,
            LvaApplicationControllers\TypeOfLicenceController::class => LvaApplicationControllerFactories\TypeOfLicenceControllerFactory::class,
            LvaApplicationControllers\VehiclesController::class => LvaApplicationControllerFactories\VehiclesControllerFactory::class,
            LvaApplicationControllers\VehiclesDeclarationsController::class => LvaApplicationControllerFactories\VehiclesDeclarationsControllerFactory::class,
            LvaApplicationControllers\VehiclesPsvController::class => LvaApplicationControllerFactories\VehiclesPsvControllerFactory::class,
            LvaApplicationControllers\WithdrawController::class => LvaApplicationControllerFactories\WithdrawControllerFactory::class,
            LvaLicenceControllers\AddressesController::class => LvaLicenceControllerFactories\AddressesControllerFactory::class,
            LvaLicenceControllers\BusinessDetailsController::class => LvaLicenceControllerFactories\BusinessDetailsControllerFactory::class,
            LvaLicenceControllers\BusinessTypeController::class => LvaLicenceControllerFactories\BusinessTypeControllerFactory::class,
            LvaLicenceControllers\CommunityLicencesController::class => LvaLicenceControllerFactories\CommunityLicencesControllerFactory::class,
            LvaLicenceControllers\ConditionsUndertakingsController::class => LvaLicenceControllerFactories\ConditionsUndertakingsControllerFactory::class,
            LvaLicenceControllers\DiscsController::class => LvaLicenceControllerFactories\DiscsControllerFactory::class,
            LvaLicenceControllers\OperatingCentresController::class => LvaLicenceControllerFactories\OperatingCentresControllerFactory::class,
            LvaLicenceControllers\OverviewController::class => LvaLicenceControllerFactories\OverviewControllerFactory::class,
            LvaLicenceControllers\PeopleController::class => LvaLicenceControllerFactories\PeopleControllerFactory::class,
            LvaLicenceControllers\SafetyController::class => LvaLicenceControllerFactories\SafetyControllerFactory::class,
            LvaLicenceControllers\TaxiPhvController::class => LvaLicenceControllerFactories\TaxiPhvControllerFactory::class,
            LvaLicenceControllers\TrailersController::class => LvaLicenceControllerFactories\TrailersControllerFactory::class,
            LvaLicenceControllers\TransportManagersController::class => LvaLicenceControllerFactories\TransportManagersControllerFactory::class,
            LvaLicenceControllers\TypeOfLicenceController::class => LvaLicenceControllerFactories\TypeOfLicenceControllerFactory::class,
            LvaLicenceControllers\VariationController::class => LvaLicenceControllerFactories\VariationControllerFactory::class,
            LvaLicenceControllers\VehiclesController::class => LvaLicenceControllerFactories\VehiclesControllerFactory::class,
            LvaLicenceControllers\VehiclesPsvController::class => LvaLicenceControllerFactories\VehiclesPsvControllerFactory::class,
            LvaVariationControllers\AddressesController::class => LvaVariationControllerFactories\AddressesControllerFactory::class,
            LvaVariationControllers\BusinessDetailsController::class => LvaVariationControllerFactories\BusinessDetailsControllerFactory::class,
            LvaVariationControllers\BusinessTypeController::class => LvaVariationControllerFactories\BusinessTypeControllerFactory::class,
            LvaVariationControllers\CommunityLicencesController::class => LvaVariationControllerFactories\CommunityLicencesControllerFactory::class,
            LvaVariationControllers\ConditionsUndertakingsController::class => LvaVariationControllerFactories\ConditionsUndertakingsControllerFactory::class,
            LvaVariationControllers\ConvictionsPenaltiesController::class => LvaVariationControllerFactories\ConvictionsPenaltiesControllerFactory::class,
            LvaVariationControllers\DeclarationsInternalController::class => LvaVariationControllerFactories\DeclarationsInternalControllerFactory::class,
            LvaVariationControllers\DiscsController::class => LvaVariationControllerFactories\DiscsControllerFactory::class,
            LvaVariationControllers\FinancialEvidenceController::class => LvaVariationControllerFactories\FinancialEvidenceControllerFactory::class,
            LvaVariationControllers\FinancialHistoryController::class => LvaVariationControllerFactories\FinancialHistoryControllerFactory::class,
            LvaVariationControllers\GrantController::class => LvaVariationControllerFactories\GrantControllerFactory::class,
            LvaVariationControllers\InterimController::class => LvaVariationControllerFactories\InterimControllerFactory::class,
            LvaVariationControllers\LicenceHistoryController::class => LvaVariationControllerFactories\LicenceHistoryControllerFactory::class,
            LvaVariationControllers\OperatingCentresController::class => LvaVariationControllerFactories\OperatingCentresControllerFactory::class,
            LvaVariationControllers\OverviewController::class => LvaVariationControllerFactories\OverviewControllerFactory::class,
            LvaVariationControllers\PeopleController::class => LvaVariationControllerFactories\PeopleControllerFactory::class,
            LvaVariationControllers\PublishController::class => LvaVariationControllerFactories\PublishControllerFactory::class,
            LvaVariationControllers\RefuseController::class => LvaVariationControllerFactories\RefuseControllerFactory::class,
            LvaVariationControllers\ReviveApplicationController::class => LvaVariationControllerFactories\ReviveApplicationControllerFactory::class,
            LvaVariationControllers\SafetyController::class => LvaVariationControllerFactories\SafetyControllerFactory::class,
            LvaVariationControllers\SubmitController::class => LvaVariationControllerFactories\SubmitControllerFactory::class,
            LvaVariationControllers\TaxiPhvController::class => LvaVariationControllerFactories\TaxiPhvControllerFactory::class,
            LvaVariationControllers\TransportManagersController::class => LvaVariationControllerFactories\TransportManagersControllerFactory::class,
            LvaVariationControllers\TypeOfLicenceController::class => LvaVariationControllerFactories\TypeOfLicenceControllerFactory::class,
            LvaVariationControllers\VehiclesController::class => LvaVariationControllerFactories\VehiclesControllerFactory::class,
            LvaVariationControllers\VehiclesDeclarationsController::class => LvaVariationControllerFactories\VehiclesDeclarationsControllerFactory::class,
            LvaVariationControllers\VehiclesPsvController::class => LvaVariationControllerFactories\VehiclesPsvControllerFactory::class,
            LvaVariationControllers\WithdrawController::class => LvaVariationControllerFactories\WithdrawControllerFactory::class,
            Olcs\Controller\IndexController::class => Olcs\Controller\Factory\IndexControllerFactory::class,
            Olcs\Controller\Messages\LicenceConversationMessagesController::class => Olcs\Controller\Factory\Messages\LicenceConversationMessagesControllerFactory::class,
            Olcs\Controller\Messages\ApplicationConversationListController::class => Olcs\Controller\Factory\Messages\ApplicationConversationListControllerFactory::class,
            Olcs\Controller\Messages\LicenceConversationListController::class=> Olcs\Controller\Factory\Messages\LicenceConversationListControllerFactory::class,
            Olcs\Controller\Messages\LicenceDisableConversationListController::class=> Olcs\Controller\Factory\Messages\LicenceDisableConversationListControllerFactory::class,
            Olcs\Controller\Messages\LicenceNewConversationController::class=> Olcs\Controller\Factory\Messages\LicenceNewConversationControllerFactory::class,
            Olcs\Controller\Messages\LicenceCloseConversationController::class => Olcs\Controller\Factory\Messages\LicenceCloseConversationControllerFactory::class,
            OperatorControllers\OperatorFeesController::class => OperatorControllerFactories\OperatorFeesControllerFactory::class,
            OperatorControllers\OperatorProcessingTasksController::class => OperatorControllerFactories\OperatorProcessingTasksControllerFactory::class,
            OperatorControllers\UnlicensedBusinessDetailsController::class => OperatorControllerFactories\UnlicensedBusinessDetailsControllerFactory::class,
            OperatorControllers\HistoryController::class => OperatorControllerFactories\HistoryControllerFactory::class,
            OperatorControllers\Cases\UnlicensedCasesOperatorController::class => OperatorControllerFactories\Cases\UnlicensedCasesOperatorControllerFactory::class,
            OperatorControllers\Docs\OperatorDocsController::class => OperatorControllerFactories\Docs\OperatorDocsControllerFactory::class,
            OperatorControllers\OperatorController::class => OperatorControllerFactories\OperatorControllerFactory::class,
            ApplicationControllers\ApplicationController::class => ApplicationControllerFactories\ApplicationControllerFactory::class,
            ApplicationControllers\Docs\ApplicationDocsController::class => ApplicationControllerFactories\Docs\ApplicationDocsControllerFactory::class,
            ApplicationControllers\Fees\ApplicationFeesController::class => ApplicationControllerFactories\Fees\ApplicationFeesControllerFactory::class,
            ApplicationControllers\Processing\ApplicationProcessingOverviewController::class => ApplicationControllerFactories\Processing\ApplicationProcessingOverviewControllerFactory::class,
            ApplicationControllers\Processing\ApplicationProcessingTasksController::class => ApplicationControllerFactories\Processing\ApplicationProcessingTasksControllerFactory::class,
            ApplicationControllers\ApplicationSchedule41Controller::class => ApplicationControllerFactories\ApplicationSchedule41ControllerFactory::class,
            BusControllers\Docs\BusDocsController::class => BusControllerFactories\Docs\BusDocsControllerFactory::class,
            BusControllers\Fees\BusFeesController::class => BusControllerFactories\Fees\BusFeesControllerFactory::class,
            BusControllers\Processing\BusProcessingTaskController::class => BusControllerFactories\Processing\BusProcessingTaskControllerFactory::class,
            CaseControllers\Docs\CaseDocsController::class => CaseControllerFactories\Docs\CaseDocsControllerFactory::class,
            CaseControllers\Processing\TaskController::class => CaseControllerFactories\Processing\TaskControllerFactory::class,
            DocumentControllers\DocumentFinaliseController::class => DocumentControllerFactories\DocumentFinaliseControllerFactory::class,
            DocumentControllers\DocumentGenerationController::class => DocumentControllerFactories\DocumentGenerationControllerFactory::class,
            DocumentControllers\DocumentRelinkController::class => DocumentControllerFactories\DocumentRelinkControllerFactory::class,
            DocumentControllers\DocumentUploadController::class => DocumentControllerFactories\DocumentUploadControllerFactory::class,
            IrhpPermitsControllers\IrhpApplicationDocsController::class => IrhpPermitsControllerFactories\IrhpApplicationDocsControllerFactory::class,
            IrhpPermitsControllers\IrhpApplicationFeesController::class => IrhpPermitsControllerFactories\IrhpApplicationFeesControllerFactory::class,
            IrhpPermitsControllers\IrhpApplicationProcessingOverviewController::class => IrhpPermitsControllerFactories\IrhpApplicationProcessingOverviewControllerFactory::class,
            IrhpPermitsControllers\IrhpApplicationProcessingTasksController::class => IrhpPermitsControllerFactories\IrhpApplicationProcessingTasksControllerFactory::class,
            LicenceControllers\Docs\LicenceDocsController::class => LicenceControllerFactories\Docs\LicenceDocsControllerFactory::class,
            LicenceControllers\LicenceController::class => LicenceControllerFactories\LicenceControllerFactory::class,
            LicenceControllers\Fees\LicenceFeesController::class => LicenceControllerFactories\Fees\LicenceFeesControllerFactory::class,
            LicenceControllers\Processing\LicenceProcessingTasksController::class => LicenceControllerFactories\Processing\LicenceProcessingTasksControllerFactory::class,
            LicenceControllers\ContinuationController::class => LicenceControllerFactories\ContinuationControllerFactory::class,
            LicenceControllers\LicenceDecisionsController::class => LicenceControllerFactories\LicenceDecisionsControllerFactory::class,
            LicenceControllers\LicenceGracePeriodsController::class => LicenceControllerFactories\LicenceGracePeriodsControllerFactory::class,
            LicenceProcessingOverviewController::class => LicenceControllerFactories\Processing\LicenceProcessingOverviewControllerFactory::class,
            Olcs\Controller\Bus\Processing\BusProcessingDecisionController::class => Olcs\Controller\Bus\Processing\BusProcessingDecisionControllerFactory::class,
            BusRegistrationController::class => BusRegistrationControllerFactory::class,
            BusServiceController::class => BusServiceControllerFactory::class,
            Olcs\Controller\Bus\Details\BusDetailsController::class => Olcs\Controller\Bus\Details\BusDetailsControllerFactory::class,
            Olcs\Controller\DisqualifyController::class => DisqualifyControllerFactory::class,
            Cases\Submission\SubmissionController::class => Cases\Submission\SubmissionControllerFactory::class,
            Olcs\Controller\Cases\Penalty\PenaltyController::class => Olcs\Controller\Cases\Penalty\PenaltyControllerFactory::class,
            CaseControllers\Overview\OverviewController::class => OverviewControllerfactory::class,
            Olcs\Controller\Cases\PublicInquiry\PiController::class => Olcs\Controller\Cases\PublicInquiry\PiControllerFactory::class,
            Cases\PublicInquiry\HearingController::class => Cases\PublicInquiry\HearingControllerFactory::class,
            Olcs\Controller\IrhpPermits\IrhpApplicationController::class => Olcs\Controller\IrhpPermits\IrhpApplicationControllerFactory::class,
            Olcs\Controller\Licence\SurrenderController::class => Olcs\Controller\Licence\SurrenderControllerFactory::class,
            LicenceProcessingInspectionRequestController::class => LicenceProcessingInspectionRequestControllerFactory::class,
            ApplicationProcessingInspectionRequestController::class => ApplicationProcessingInspectionRequestControllerFactory::class,
            LicenceProcessingPublicationsController::class => LicenceProcessingPublicationsControllerfactory::class,
            Olcs\Controller\Operator\OperatorLicencesApplicationsController::class => Olcs\Controller\Operator\OperatorLicencesApplicationsControllerFactory::class,
            Olcs\Controller\Operator\OperatorPeopleController::class => Olcs\Controller\Operator\OperatorPeopleControllerFactory::class,
            Olcs\Controller\Operator\OperatorProcessingNoteController::class => Olcs\Controller\Operator\OperatorProcessingNoteControllerFactory::class,
            Olcs\Controller\TransportManager\Details\TransportManagerDetailsCompetenceController::class => Olcs\Controller\TransportManager\Details\TransportManagerDetailsCompetenceControllerFactory::class,
            TransportManagerDetailsDetailController::class => TransportManagerDetailsDetailControllerFactory::class,
            Olcs\Controller\TransportManager\Details\TransportManagerDetailsEmploymentController::class => Olcs\Controller\TransportManager\Details\TransportManagerDetailsEmploymentControllerFactory::class,
            Olcs\Controller\TransportManager\HistoricTm\HistoricTmController::class => Olcs\Controller\TransportManager\HistoricTm\HistoricTmControllerFactory::class,
            Olcs\Controller\TransportManager\Processing\PublicationController::class => Olcs\Controller\TransportManager\Processing\PublicationControllerFactory::class,
            Olcs\Controller\Cases\Conviction\ConvictionController::class => Olcs\Controller\Cases\Conviction\ConvictionControllerFactory::class,
            Olcs\Controller\Cases\Conviction\LegacyOffenceController::class => Olcs\Controller\Cases\Conviction\LegacyOffenceControllerFactory::class,
            Olcs\Controller\Cases\AnnualTestHistory\AnnualTestHistoryController::class => Olcs\Controller\Cases\AnnualTestHistory\AnnualTestHistoryControllerFactory::class,
            Olcs\Controller\Application\Processing\ApplicationProcessingNoteController::class => Olcs\Controller\Application\Processing\ApplicationProcessingNoteControllerFactory::class,
            Olcs\Controller\Cases\Prohibition\ProhibitionController::class => Olcs\Controller\Cases\Prohibition\ProhibitionControllerFactory::class,
            Olcs\Controller\Cases\Prohibition\ProhibitionDefectController::class => Olcs\Controller\Cases\Prohibition\ProhibitionDefectControllerFactory::class,
            Olcs\Controller\Cases\Penalty\SiController::class => Olcs\Controller\Cases\Penalty\SiControllerFactory::class,
            Olcs\Controller\Cases\Complaint\ComplaintController::class => Olcs\Controller\Cases\Complaint\ComplaintControllerFactory::class,
            Olcs\Controller\Cases\Complaint\EnvironmentalComplaintController::class => Olcs\Controller\Cases\Complaint\EnvironmentalComplaintControllerFactory::class,
            Olcs\Controller\Cases\NonPublicInquiry\NonPublicInquiryController::class => Olcs\Controller\Cases\NonPublicInquiry\NonPublicInquiryControllerFactory::class,
            Olcs\Controller\Cases\Submission\DecisionController::class => Olcs\Controller\Cases\Submission\DecisionControllerFactory::class,
            Olcs\Controller\Cases\Processing\DecisionsController::class => Olcs\Controller\Cases\Processing\DecisionsControllerFactory::class,
            Olcs\Controller\Cases\Processing\RevokeController::class => Olcs\Controller\Cases\Processing\RevokeControllerFactory::class,
            Olcs\Controller\Cases\Processing\ReadHistoryController::class => Olcs\Controller\Cases\Processing\ReadHistoryControllerFactory::class,
            Olcs\Controller\Cases\ConditionUndertaking\ConditionUndertakingController::class => Olcs\Controller\Cases\ConditionUndertaking\ConditionUndertakingControllerFactory::class,
            Olcs\Controller\Cases\Impounding\ImpoundingController::class => Olcs\Controller\Cases\Impounding\ImpoundingControllerFactory::class,
            Cases\Statement\StatementController::class => Cases\Statement\StatementControllerFactory::class,
            Olcs\Controller\TransportManager\Processing\HistoryController::class => Olcs\Controller\TransportManager\Processing\HistoryControllerFactory::class,
            Olcs\Controller\Licence\Processing\HistoryController::class => LicenceControllers\Processing\HistoryControllerFactory::class,
            Olcs\Controller\IrhpPermits\ChangeHistoryController::class => Olcs\Controller\IrhpPermits\ChangeHistoryControllerFactory::class,
            Olcs\Controller\IrhpPermits\IrhpApplicationProcessingHistoryController::class => Olcs\Controller\IrhpPermits\IrhpApplicationProcessingHistoryControllerFactory::class,
            Olcs\Controller\Cases\Processing\HistoryController::class => Olcs\Controller\Cases\Processing\HistoryControllerFactory::class,
            Olcs\Controller\Bus\Processing\HistoryController::class => Olcs\Controller\Bus\Processing\HistoryControllerFactory::class,
            Olcs\Controller\Application\Processing\HistoryController::class => Olcs\Controller\Application\Processing\HistoryControllerFactory::class,
            Olcs\Controller\Application\Processing\ReadHistoryController::class => Olcs\Controller\Application\Processing\ReadHistoryControllerFactory::class,
            Olcs\Controller\Cases\Opposition\OppositionController::class => Olcs\Controller\Cases\Opposition\OppositionControllerFactory::class,
            Olcs\Controller\IrhpPermits\ApplicationController::class => Olcs\Controller\IrhpPermits\ApplicationControllerFactory::class,
            Olcs\Controller\IrhpPermits\IrhpPermitController::class => Olcs\Controller\IrhpPermits\IrhpPermitControllerFactory::class,
            Olcs\Controller\IrhpPermits\PermitController::class => Olcs\Controller\IrhpPermits\PermitControllerFactory::class,
            Cases\Submission\SubmissionSectionCommentController::class => Cases\Submission\SubmissionSectionCommentControllerFactory::class,
            Cases\Submission\RecommendationController::class => Cases\Submission\RecommendationControllerFactory::class,
            Cases\Submission\ProcessSubmissionController::class => Cases\Submission\ProcessSubmissionControllerFactory::class,
            Cases\Processing\NoteController::class => Cases\Processing\NoteControllerFactory::class,
            Olcs\Controller\TransportManager\TransportManagerCaseController::class => Olcs\Controller\TransportManager\TransportManagerCaseControllerFactory::class,
            Olcs\Controller\Cases\Processing\DecisionsDeclareUnfitController::class => Olcs\Controller\Cases\Processing\DecisionsDeclareUnfitControllerFactory::class,
            Olcs\Controller\Bus\Processing\BusProcessingNoteController::class => Olcs\Controller\Bus\Processing\BusProcessingNoteControllerFactory::class,
            Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryController::class => Olcs\Controller\Bus\Processing\BusProcessingRegistrationHistoryControllerFactory::class,
            Olcs\Controller\Bus\Processing\ReadHistoryController::class => Olcs\Controller\Bus\Processing\ReadHistoryControllerFactory::class,
            Olcs\Controller\Bus\Short\BusShortController::class => Olcs\Controller\Bus\Short\BusShortControllerFactory::class,
            Olcs\Controller\Bus\BusRequestMapController::class => Olcs\Controller\Bus\BusRequestMapControllerFactory::class,
            Olcs\Controller\Cases\Hearing\AppealController::class => Olcs\Controller\Cases\Hearing\AppealControllerFactory::class,
            Olcs\Controller\Cases\Hearing\HearingAppealController::class => Olcs\Controller\Cases\Hearing\HearingAppealControllerFactory::class,
            Olcs\Controller\Cases\Hearing\StayController::class => Olcs\Controller\Cases\Hearing\StayControllerFactory::class,
            Olcs\Controller\Cases\Processing\DecisionsNoFurtherActionController::class => Olcs\Controller\Cases\Processing\DecisionsNoFurtherActionControllerFactory::class,
            Olcs\Controller\Cases\Processing\DecisionsReputeNotLostController::class => Olcs\Controller\Cases\Processing\DecisionsReputeNotLostControllerFactory::class,
            Olcs\Controller\IrhpPermits\IrhpApplicationProcessingNoteController::class => Olcs\Controller\IrhpPermits\IrhpApplicationProcessingNoteControllerFactory::class,
            Olcs\Controller\IrhpPermits\IrhpApplicationProcessingReadHistoryController::class => Olcs\Controller\IrhpPermits\IrhpApplicationProcessingReadHistoryControllerFactory::class,
            Olcs\Controller\Licence\Processing\LicenceProcessingNoteController::class => Olcs\Controller\Licence\Processing\LicenceProcessingNoteControllerFactory::class,
            Olcs\Controller\Licence\Processing\ReadHistoryController::class => Olcs\Controller\Licence\Processing\ReadHistoryControllerFactory::class,
            Olcs\Controller\Licence\BusRegistrationController::class => Olcs\Controller\Licence\BusRegistrationControllerFactory::class,
            Olcs\Controller\Operator\Processing\ReadHistoryController::class => Olcs\Controller\Operator\Processing\ReadHistoryControllerFactory::class,
            Olcs\Controller\Operator\OperatorIrfoDetailsController::class => Olcs\Controller\Operator\OperatorIrfoDetailsControllerFactory::class,
            Olcs\Controller\Operator\OperatorIrfoGvPermitsController::class => Olcs\Controller\Operator\OperatorIrfoGvPermitsControllerFactory::class,
            Olcs\Controller\Operator\OperatorIrfoPsvAuthorisationsController::class => Olcs\Controller\Operator\OperatorIrfoPsvAuthorisationsControllerFactory::class,
            Olcs\Controller\Operator\OperatorUsersController::class => Olcs\Controller\Operator\OperatorUsersControllerFactory::class,
            Olcs\Controller\Operator\UnlicensedOperatorVehiclesController::class => Olcs\Controller\Operator\UnlicensedOperatorVehiclesControllerFactory::class,
            Olcs\Controller\Sla\RevocationsSlaController::class => Olcs\Controller\Sla\RevocationsSlaControllerFactory::class,
            Olcs\Controller\TransportManager\Processing\ReadHistoryController::class => Olcs\Controller\TransportManager\Processing\ReadHistoryControllerFactory::class,
            Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteController::class => Olcs\Controller\TransportManager\Processing\TransportManagerProcessingNoteControllerFactory::class,
            Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController::class => TransportManagerProcessingTaskControllerFactory::class,
            Olcs\Controller\Sla\CaseDocumentSlaTargetDateController::class => Olcs\Controller\Sla\CaseDocumentSlaTargetDateControllerFactory::class,
            Olcs\Controller\Sla\LicenceDocumentSlaTargetDateController::class => Olcs\Controller\Sla\LicenceDocumentSlaTargetDateControllerFactory::class,
            TransportManagerControllers\TransportManagerController::class => TransportManagerControllerFactories\TransportManagerControllerFactory::class,
            TaskController::class => TaskControllerFactory::class,
            ApplicationProcessingPublicationsController::class => ApplicationProcessingPublicationsControllerFactory::class,
            SearchController::class => SearchControllerFactory::class,
            OperatorBusinessDetailsController::class => OperatorBusinessDetailsControllerFactory::class,
            \Olcs\Controller\SplitScreenController::class => SplitScreenControllerFactory::class,
            TransportManagerDocumentController::class => TransportManagerDocumentControllerFactory::class,
            TransportManagerDetailsPreviousHistoryController::class => TransportManagerDetailsPreviousHistoryControllerFactory::class,
            VariationSchedule41Controller::class => VariationSchedule41ControllerFactory::class,
        ],
        'aliases' => [
            'LvaApplication' => Olcs\Controller\Lva\Application\OverviewController::class,
            'LvaApplication/TypeOfLicence' => LvaApplicationControllers\TypeOfLicenceController::class,
            'LvaApplication/BusinessType' => LvaApplicationControllers\BusinessTypeController::class,
            'LvaApplication/BusinessDetails' => LvaApplicationControllers\BusinessDetailsController::class,
            'LvaApplication/Addresses' => LvaApplicationControllers\AddressesController::class,
            'LvaApplication/People' => LvaApplicationControllers\PeopleController::class,
            'LvaApplication/OperatingCentres' => LvaApplicationControllers\OperatingCentresController::class,
            'LvaApplication/FinancialEvidence' => LvaApplicationControllers\FinancialEvidenceController::class,
            'LvaApplication/TransportManagers' => LvaApplicationControllers\TransportManagersController::class,
            'LvaApplication/Vehicles' => LvaApplicationControllers\VehiclesController::class,
            'LvaApplication/VehiclesPsv' => LvaApplicationControllers\VehiclesPsvController::class,
            'LvaApplication/Safety' => LvaApplicationControllers\SafetyController::class,
            'LvaApplication/CommunityLicences' => LvaApplicationControllers\CommunityLicencesController::class,
            'LvaApplication/FinancialHistory' => LvaApplicationControllers\FinancialHistoryController::class,
            'LvaApplication/LicenceHistory' => LvaApplicationControllers\LicenceHistoryController::class,
            'LvaApplication/ConvictionsPenalties' => LvaApplicationControllers\ConvictionsPenaltiesController::class,
            'LvaApplication/TaxiPhv' => LvaApplicationControllers\TaxiPhvController::class,
            'LvaApplication/ConditionsUndertakings' => LvaApplicationControllers\ConditionsUndertakingsController::class,
            'LvaApplication/VehiclesDeclarations' => LvaApplicationControllers\VehiclesDeclarationsController::class,
            'LvaApplication/Review' => \Common\Controller\Lva\ReviewController::class,
            'LvaApplication/Grant' => LvaApplicationControllers\GrantController::class,
            'LvaApplication/Withdraw' => LvaApplicationControllers\WithdrawController::class,
            'LvaApplication/Refuse' => LvaApplicationControllers\RefuseController::class,
            'LvaApplication/NotTakenUp' => LvaApplicationControllers\NotTakenUpController::class,
            'LvaApplication/ReviveApplication' => LvaApplicationControllers\ReviveApplicationController::class,
            'LvaApplication/DeclarationsInternal' => LvaApplicationControllers\DeclarationsInternalController::class,
            'LvaApplication/Publish' => LvaApplicationControllers\PublishController::class,
            'LvaApplication/Submit' => LvaApplicationControllers\SubmitController::class,
            'VariationSchedule41Controller' => \Olcs\Controller\Variation\VariationSchedule41Controller::class,
            'LvaLicence' => LvaLicenceControllers\OverviewController::class,
            'LvaLicence/TypeOfLicence' => LvaLicenceControllers\TypeOfLicenceController::class,
            'LvaLicence/BusinessType' => LvaLicenceControllers\BusinessTypeController::class,
            'LvaLicence/BusinessDetails' => LvaLicenceControllers\BusinessDetailsController::class,
            'LvaLicence/Addresses' => LvaLicenceControllers\AddressesController::class,
            'LvaLicence/People' => LvaLicenceControllers\PeopleController::class,
            'LvaLicence/OperatingCentres' => LvaLicenceControllers\OperatingCentresController::class,
            'LvaLicence/TransportManagers' => LvaLicenceControllers\TransportManagersController::class,
            'LvaLicence/Vehicles' => LvaLicenceControllers\VehiclesController::class,
            'LvaLicence/VehiclesPsv' => LvaLicenceControllers\VehiclesPsvController::class,
            'LvaLicence/Safety' => LvaLicenceControllers\SafetyController::class,
            'LvaLicence/CommunityLicences' => LvaLicenceControllers\CommunityLicencesController::class,
            'LvaLicence/TaxiPhv' => LvaLicenceControllers\TaxiPhvController::class,
            'LvaLicence/Discs' => LvaLicenceControllers\DiscsController::class,
            'LvaLicence/ConditionsUndertakings' => LvaLicenceControllers\ConditionsUndertakingsController::class,
            'LvaLicence/Variation' => LvaLicenceControllers\VariationController::class,
            'LvaLicence/Trailers' => LvaLicenceControllers\TrailersController::class,
            'LvaVariation' => Olcs\Controller\Lva\Variation\OverviewController::class,
            'LvaVariation/TypeOfLicence' => LvaVariationControllers\TypeOfLicenceController::class,
            'LvaVariation/BusinessType' => LvaVariationControllers\BusinessTypeController::class,
            'LvaVariation/BusinessDetails' => LvaVariationControllers\BusinessDetailsController::class,
            'LvaVariation/Addresses' => LvaVariationControllers\AddressesController::class,
            'LvaVariation/People' => LvaVariationControllers\PeopleController::class,
            'LvaVariation/OperatingCentres' => LvaVariationControllers\OperatingCentresController::class,
            'LvaVariation/TransportManagers' => LvaVariationControllers\TransportManagersController::class,
            'LvaVariation/Vehicles' => LvaVariationControllers\VehiclesController::class,
            'LvaVariation/VehiclesPsv' => LvaVariationControllers\VehiclesPsvController::class,
            'LvaVariation/Safety' => LvaVariationControllers\SafetyController::class,
            'LvaVariation/CommunityLicences' => LvaVariationControllers\CommunityLicencesController::class,
            'LvaVariation/TaxiPhv' => LvaVariationControllers\TaxiPhvController::class,
            'LvaVariation/Discs' => LvaVariationControllers\DiscsController::class,
            'LvaVariation/ConditionsUndertakings' => LvaVariationControllers\ConditionsUndertakingsController::class,
            'LvaVariation/FinancialEvidence' => LvaVariationControllers\FinancialEvidenceController::class,
            'LvaVariation/FinancialHistory' => LvaVariationControllers\FinancialHistoryController::class,
            'LvaVariation/LicenceHistory' => LvaVariationControllers\LicenceHistoryController::class,
            'LvaVariation/ConvictionsPenalties' => LvaVariationControllers\ConvictionsPenaltiesController::class,
            'LvaVariation/VehiclesDeclarations' => LvaVariationControllers\VehiclesDeclarationsController::class,
            'LvaVariation/Review' => \Common\Controller\Lva\ReviewController::class,
            'LvaVariation/Grant' => LvaVariationControllers\GrantController::class,
            'LvaVariation/Withdraw' => LvaVariationControllers\WithdrawController::class,
            'LvaVariation/Refuse' => LvaVariationControllers\RefuseController::class,
            'LvaVariation/Submit' => Olcs\Controller\Lva\Variation\SubmitController::class,
            'LvaVariation/Revive' => LvaVariationControllers\ReviveApplicationController::class,
            'LvaVariation/DeclarationsInternal' => LvaVariationControllers\DeclarationsInternalController::class,
            'LvaVariation/Publish' => LvaVariationControllers\PublishController::class,
            'OperatorHistoryController' => HistoryController::class,
            'LicenceController' => LicenceControllers\LicenceController::class,
            'LicenceDocsController' => LicenceControllers\Docs\LicenceDocsController::class,
            'CaseOffenceController' => 'Olcs\Controller\Cases\Conviction\OffenceController',
            'CasePublicInquiryController' => 'Olcs\Controller\Cases\PublicInquiry\PublicInquiryController',
            'DefaultController' => 'Olcs\Olcs\Placeholder\Controller\DefaultController',
            'DocumentController' => 'Olcs\Controller\Document\DocumentController',
            'LicenceDecisionsController' => LicenceControllers\LicenceDecisionsController::class,
            'LicencePermitsController' => 'Olcs\Controller\Licence\Permits\LicencePermitsController',
            'LicenceGracePeriodsController' => LicenceControllers\LicenceGracePeriodsController::class,
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
            'LicenceDetailsConditionUndertakingController' => 'Olcs\Controller\Licence\Details\ConditionUndertakingController',
            'LicenceDetailsTaxiPhvController' => 'Olcs\Controller\Licence\Details\TaxiPhvController',
            'LicenceProcessingPublicationsController' => 'Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsController',
            'ApplicationProcessingOverviewController' => 'Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController',
            'BusDetailsServiceController' => 'Olcs\Controller\Bus\Details\BusDetailsServiceController',
            'BusDetailsStopController' => 'Olcs\Controller\Bus\Details\BusDetailsStopController',
            'BusDetailsTaController' => 'Olcs\Controller\Bus\Details\BusDetailsTaController',
            'BusDetailsQualityController' => 'Olcs\Controller\Bus\Details\BusDetailsQualityController',
            'BusShortPlaceholderController' => 'Olcs\Controller\Bus\Short\BusShortPlaceholderController',
            'BusRouteController' => 'Olcs\Controller\Bus\Route\BusRouteController',
            'BusRoutePlaceholderController' => 'Olcs\Controller\Bus\Route\BusRoutePlaceholderController',
            'BusTrcController' => 'Olcs\Controller\Bus\Trc\BusTrcController',
            'BusTrcPlaceholderController' => 'Olcs\Controller\Bus\Trc\BusTrcPlaceholderController',
            'BusDocsPlaceholderController' => 'Olcs\Controller\Bus\Docs\BusDocsPlaceholderController',
            'BusProcessingTaskController' => 'Olcs\Controller\Bus\Processing\BusProcessingTaskController',
            'BusFeesController' => 'Olcs\Controller\Bus\Fees\BusFeesController',
            'BusFeesPlaceholderController' => 'Olcs\Controller\Bus\Fees\BusFeesPlaceholderController',
            'OperatorDocsController' => 'Olcs\Controller\Operator\Docs\OperatorDocsController',
            'UnlicensedCasesOperatorController' => 'Olcs\Controller\Operator\Cases\UnlicensedCasesOperatorController',
            'OperatorFeesController' => 'Olcs\Controller\Operator\OperatorFeesController',
            'TMController' => TransportManagerController::class,
            'HistoricTmController' => Olcs\Controller\TransportManager\HistoricTm\HistoricTmController::class,
            'TMDetailsDetailController' => TransportManagerDetailsDetailController::class,
            'TMDetailsPreviousHistoryController' => TransportManagerDetailsPreviousHistoryController::class,
            'TMProcessingPublicationController' => Olcs\Controller\TransportManager\Processing\PublicationController::class,
            'TMProcessingTaskController' => TransportManagerProcessingTaskController::class,
            'TMDocumentController' => TmCntr\TransportManagerDocumentController::class,
            'InterimApplicationController' => 'Olcs\Controller\Lva\Application\InterimController',
            'InterimVariationController' => 'Olcs\Controller\Lva\Variation\InterimController',
            'SplitScreenController' => \Olcs\Controller\SplitScreenController::class,
            'CaseHistoryController' => Olcs\Controller\Cases\Processing\HistoryController::class,
            'CaseReadHistoryController' => 'Olcs\Controller\Cases\Processing\ReadHistoryController',
            'BusRegHistoryController' => 'Olcs\Controller\Bus\Processing\HistoryController',
            'BusRegReadHistoryController' => 'Olcs\Controller\Bus\Processing\ReadHistoryController',
            'LicenceHistoryController' => 'Olcs\Controller\Licence\Processing\HistoryController',
            'LicenceReadHistoryController' => 'Olcs\Controller\Licence\Processing\ReadHistoryController',
            'TransportManagerHistoryController' => TmCntr\Processing\HistoryController::class,
            'TransportManagerReadHistoryController' => 'Olcs\Controller\TransportManager\Processing\ReadHistoryController',
            'ApplicationHistoryController' => ApplicationControllers\Processing\HistoryController::class,
            'ApplicationReadHistoryController' => ApplicationControllers\Processing\ReadHistoryController::class,
            'OperatorReadHistoryController' => OperatorControllers\Processing\ReadHistoryController::class,
            'CaseDocumentSlaTargetDateController' => CaseDocumentSlaTargetDateController::class ,
            'LicenceDocumentSlaTargetDateController' => LicenceDocumentSlaTargetDateController::class,
            'IrhpDocsController' => 'Olcs\Controller\IrhpPermits\IrhpDocsController',
        ]
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'Olcs\Mvc\Controller\Plugin\Confirm' => 'Olcs\Mvc\Controller\Plugin\Confirm',
            ViewBuilder::class => ViewBuilder::class,
        ),
        'factories' => [
            Script::class => ScriptFactory::class,
            Placeholder::class => PlaceholderFactory::class,
            Table::class => TableFactory::class,
        ],
        'aliases' => array(
            'confirm' => 'Olcs\Mvc\Controller\Plugin\Confirm',
            'viewBuilder' => ViewBuilder::class,
            'script' => Script::class,
            'placeholder' => Placeholder::class,
            'table' => Table::class,

        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/base.phtml',
            'auth/layout' => __DIR__ . '/../view/layout/signin.phtml',
            'pages/lva-details' => __DIR__ . '/../view/sections/lva/lva-details.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/403' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
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
        'invokables' => [
            'piListData' => Olcs\View\Helper\PiListData::class,
            'formSubmissionSections' => Olcs\Form\View\Helper\SubmissionSections::class,
            'submissionSectionDetails' => Olcs\View\Helper\SubmissionSectionDetails::class,
            'SubmissionSectionOverview' => Olcs\View\Helper\SubmissionSectionOverview::class,
            'surrenderDetails' => Olcs\View\Helper\SurrenderDetails::class,
        ],
        'factories' => [
            'addressFormat' => Olcs\View\Helper\AddressFactory::class,
            'SubmissionSectionTable' => SubmissionSectionTableFactory::class,
            'SubmissionSectionMultipleTables' => SubmissionSectionMultipleTablesFactory::class,
            'Olcs\View\Helper\SlaIndicator' => SlaIndicator::class,
            'showMarkers' => Olcs\View\Helper\MarkersFactory::class,
            'showVersion' => Olcs\View\Helper\Factory\VersionFactory::class,
            \Common\View\Helper\EscapeHtml::class => \Common\View\Factory\Helper\EscapeHtmlFactory::class,
            \Common\Form\View\Helper\FormElement::class => \Common\Form\View\Helper\FormElementFactory::class,
        ],
        'aliases' => [
            'formElement' => \Common\Form\View\Helper\FormElement::class,
            'formelement' => \Common\Form\View\Helper\FormElement::class,
            'slaIndicator' => 'Olcs\View\Helper\SlaIndicator',
            'escapeHtml' => \Common\View\Helper\EscapeHtml::class
        ]
    ),
    'form' => [
        'element' => [
            'renderers' => [
                SubmissionSections::class => 'formSubmissionSections',
            ],
        ],
    ],
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
    'asset_path' => '//dev_dvsa-static.web01.olcs.mgt.mtpdvsa',
    'service_manager' => array(
        'aliases' => [
            'RouteParamsListener' => \Olcs\Listener\RouteParams::class,
            'right-sidebar' => 'Olcs\Navigation\RightHandNavigation',
            'HeaderSearchListener' => HeaderSearch::class,
            'Helper\ApplicationOverview' => HelperService\ApplicationOverviewHelperService::class,
            'Helper\LicenceOverview' => HelperService\LicenceOverviewHelperService::class,
            'Processing\CreateVariation' => ProcessingService\CreateVariationProcessingServiceFactory::class,
            'LicenceListener' => LicenceListener::class
        ],
        'invokables' => [
            'ApplicationUtility' => 'Olcs\Service\Utility\ApplicationUtility',
            Olcs\Service\Permits\Bilateral\MoroccoFieldsetPopulator::class =>
                Olcs\Service\Permits\Bilateral\MoroccoFieldsetPopulator::class,
            \Olcs\Helper\ApplicationProcessingHelper::class => \Olcs\Helper\ApplicationProcessingHelper::class,
            'Router' => Laminas\Router\Http\TreeRouteStack::class,
        ],
        'abstract_factories' => [
            StorageCacheAbstractServiceFactory::class,
        ],
        'factories' => array(
            RouteParam\Licence::class => RouteParam\Licence::class,
            ProcessingService\CreateVariationProcessingService::class => ProcessingService\CreateVariationProcessingServiceFactory::class,

            DataService\AbstractPublicInquiryDataServices::class => DataService\AbstractPublicInquiryDataServicesFactory::class,

            HelperService\ApplicationOverviewHelperService::class => HelperService\ApplicationOverviewHelperServiceFactory::class,
            HelperService\LicenceOverviewHelperService::class => HelperService\LicenceOverviewHelperServiceFactory::class,

            MarkerService::class => MarkerService::class,
            MarkerPluginManager::class =>
                MarkerPluginManagerFactory::class,
            'Olcs\Listener\RouteParam\BusRegId' => 'Olcs\Listener\RouteParam\BusRegId',
            'Olcs\Listener\RouteParam\BusRegAction' => 'Olcs\Listener\RouteParam\BusRegAction',
            'Olcs\Listener\RouteParam\BusRegMarker' => 'Olcs\Listener\RouteParam\BusRegMarker',
            'Olcs\Listener\RouteParam\TransportManagerMarker' => 'Olcs\Listener\RouteParam\TransportManagerMarker',
            'Olcs\Listener\RouteParam\Action' => 'Olcs\Listener\RouteParam\Action',
            'Olcs\Listener\RouteParam\TransportManager' => 'Olcs\Listener\RouteParam\TransportManager',
            ApplicationListener::class => ApplicationListener::class,
            ApplicationFurniture::class => ApplicationFurniture::class,
            LicenceFurniture::class => LicenceFurniture::class,
            OrganisationFurniture::class => OrganisationFurniture::class,
            VariationFurniture::class => VariationFurniture::class,
            BusRegFurniture::class => BusRegFurniture::class,
            CasesFurniture::class => CasesFurniture::class,
            SubmissionsFurniture::class => SubmissionsFurniture::class,
            TransportManagerFurniture::class => TransportManagerFurniture::class,
            IrhpApplicationFurniture::class => IrhpApplicationFurniture::class,
            Olcs\Listener\RouteParam\Cases::class => Olcs\Listener\RouteParam\Cases::class,
            'Olcs\Listener\RouteParam\CaseMarker' => 'Olcs\Listener\RouteParam\CaseMarker',
            RouteParam\Organisation::class => RouteParam\Organisation::class,
            'Olcs\Navigation\RightHandNavigation' => 'Olcs\Navigation\RightHandNavigationFactory',
            HeaderSearch::class => HeaderSearch::class,
            Olcs\Data\Mapper\BilateralApplicationValidationModifier::class =>
                Olcs\Data\Mapper\BilateralApplicationValidationModifierFactory::class,
            Olcs\Data\Mapper\IrhpApplication::class =>
                Olcs\Data\Mapper\IrhpApplicationFactory::class,

            Olcs\Service\Permits\Bilateral\ApplicationFormPopulator::class =>
                Olcs\Service\Permits\Bilateral\ApplicationFormPopulatorFactory::class,
            Olcs\Service\Permits\Bilateral\CountryFieldsetGenerator::class =>
                Olcs\Service\Permits\Bilateral\CountryFieldsetGeneratorFactory::class,
            Olcs\Service\Permits\Bilateral\PeriodFieldsetGenerator::class =>
                Olcs\Service\Permits\Bilateral\PeriodFieldsetGeneratorFactory::class,
            Olcs\Service\Permits\Bilateral\StandardFieldsetPopulator::class =>
                Olcs\Service\Permits\Bilateral\StandardFieldsetPopulatorFactory::class,
            Olcs\Service\Permits\Bilateral\NoOfPermitsElementGenerator::class =>
                Olcs\Service\Permits\Bilateral\NoOfPermitsElementGeneratorFactory::class,

            WebDavJsonWebTokenGenerationService::class =>
                WebDavJsonWebTokenGenerationServiceFactory::class,

            Auth\Adapter\InternalCommandAdapter::class => Auth\Adapter\InternalCommandAdapterFactory::class,
            'RoutePluginManager' => Laminas\Router\RoutePluginManagerFactory::class,
            \Olcs\Listener\RouteParams::class => \Olcs\Listener\RouteParamsFactory::class,
        )
    ),
    'form_elements' => [
        'factories' => [
            'SubmissionSections' => SubmissionSectionsFactory::class,
            'Olcs\Form\Element\SearchFilterFieldset' => SearchFilterFieldsetFactory::class,
            'Olcs\Form\Element\SearchDateRangeFieldset' => SearchDateRangeFieldsetFactory::class,
            Olcs\Form\Element\SearchOrderFieldset::class => Olcs\Form\Element\SearchOrderFieldsetFactory::class,
        ],
        'aliases' => [
            'SlaDateSelect' => 'Olcs\Form\Element\SlaDateSelect',
            'SlaDateTimeSelect' => 'Olcs\Form\Element\SlaDateTimeSelect',
            'SearchFilterFieldset' => 'Olcs\Form\Element\SearchFilterFieldset',
            'SearchDateRangeFieldset' => 'Olcs\Form\Element\SearchDateRangeFieldset'
        ]
    ],
    'route_param_listeners' => [
        CaseControllerInterface::class => [
            RouteParam\CasesFurniture::class,
            RouteParam\Cases::class,
            RouteParam\Licence::class,
            RouteParam\CaseMarker::class,
            RouteParam\Application::class,
            RouteParam\TransportManager::class,
            RouteParam\Action::class,
            HeaderSearch::class
        ],
        SubmissionControllerInterface::class => [
            RouteParam\SubmissionsFurniture::class,
            RouteParam\Cases::class,
            RouteParam\Licence::class,
            RouteParam\CaseMarker::class,
            RouteParam\Application::class,
            RouteParam\TransportManager::class,
            RouteParam\Action::class,
            HeaderSearch::class
        ],
        ApplicationControllerInterface::class => [
            RouteParam\ApplicationFurniture::class,
            RouteParam\Application::class,
            RouteParam\Cases::class,
            RouteParam\Licence::class,
            RouteParam\CaseMarker::class,
            RouteParam\TransportManager::class,
            RouteParam\Action::class,
            HeaderSearch::class
        ],
        // @NOTE This needs to be mostly the same as ApplicationControllerInterface except for the furniture
        VariationControllerInterface::class => [
            RouteParam\VariationFurniture::class,
            RouteParam\Application::class,
            RouteParam\Cases::class,
            RouteParam\Licence::class,
            RouteParam\CaseMarker::class,
            RouteParam\TransportManager::class,
            RouteParam\Action::class,
            HeaderSearch::class
        ],
        BusRegControllerInterface::class => [
            RouteParam\BusRegFurniture::class,
            RouteParam\CaseMarker::class,
            RouteParam\Application::class,
            RouteParam\BusRegId::class,
            RouteParam\BusRegAction::class,
            RouteParam\BusRegMarker::class,
            RouteParam\Licence::class,
            HeaderSearch::class
        ],
        TransportManagerControllerInterface::class => [
            RouteParam\TransportManagerFurniture::class,
            RouteParam\TransportManager::class,
            RouteParam\CaseMarker::class,
            RouteParam\TransportManagerMarker::class,
            HeaderSearch::class
        ],
        LicenceControllerInterface::class => [
            RouteParam\LicenceFurniture::class,
            RouteParam\Licence::class,
        ],
        OperatorControllerInterface::class => [
            RouteParam\Organisation::class,
            RouteParam\OrganisationFurniture::class,
        ],
        IrhpApplicationControllerInterface::class => [
            RouteParam\IrhpApplicationFurniture::class,
            RouteParam\LicenceFurniture::class,
            RouteParam\Licence::class,
        ],
    ],
    'search' => [
        'invokables' => [
            'licence' => LicenceSearch::class,
            'application' => Application::class,
            'case' => \Common\Data\Object\Search\Cases::class,
            'psv_disc' => PsvDisc::class,
            'vehicle' => Vehicle::class,
            'address' => Address::class,
            'bus_reg' => BusReg::class,
            'people' => People::class,
            'user' => User::class,
            'publication' => Publication::class,
            'irfo' => IrfoOrganisation::class,
        ]
    ],
    'data_services' => [
        'factories' => [
            DataService\ActionToBeTaken::class => CommonDataService\RefDataFactory::class,
            DataService\ApplicationStatus::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\AssignedToList::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\BusNoticePeriod::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\BusServiceType::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\Cases::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\Category::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\DocumentCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\DocumentCategoryWithDocs::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\DocumentSubCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\DocumentSubCategoryWithDocs::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\EmailTemplateCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\IrfoCountry::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\IrfoGvPermitType::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\IrfoPsvAuthType::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\IrhpPermitPrintCountry::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\IrhpPermitPrintRangeType::class => DataService\IrhpPermitPrintRangeTypeFactory::class,
            DataService\IrhpPermitPrintStock::class => DataService\IrhpPermitPrintStockFactory::class,
            DataService\IrhpPermitPrintType::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\Licence::class => DataService\LicenceFactory::class,
            DataService\OperatingCentresForInspectionRequest::class => DataService\OperatingCentresForInspectionRequestFactory::class,
            DataService\PaymentType::class => CommonDataService\RefDataFactory::class,
            DataService\PresidingTc::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\Printer::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\ReportEmailTemplate::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\ReportLetterTemplate::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\ScannerCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\ScannerSubCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\SiPenaltyType::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\SubCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\SubCategoryDescription::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\Submission::class => DataService\SubmissionFactory::class,
            DataService\SubmissionActionTypes::class => DataService\SubmissionActionTypesFactory::class,
            DataService\TaskCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\TaskSubCategory::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\Team::class => DataService\TeamFactory::class,
            DataService\User::class => CommonDataService\AbstractDataServiceFactory::class,
            DataService\UserInternalTeamList::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\UserListInternal::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\UserListInternalExcludingLimitedReadOnlyUsers::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\UserListInternalExcludingLimitedReadOnlyUsersSorted::class => CommonDataService\AbstractListDataServiceFactory::class,
            DataService\UserWithName::class => CommonDataService\AbstractDataServiceFactory::class,
            CommonDataService\Search\Search::class => CommonDataService\Search\SearchFactory::class,
            DataService\ImpoundingLegislation::class => DataService\ImpoundingLegislationFactory::class,
            DataService\LicenceDecisionLegislation::class => DataService\LicenceDecisionLegislationFactory::class,
            DataService\PublicInquiryDecision::class => DataService\AbstractPublicInquiryDataFactory::class,
            DataService\PublicInquiryDefinition::class => DataService\AbstractPublicInquiryDataFactory::class,
            DataService\PublicInquiryReason::class => DataService\AbstractPublicInquiryDataFactory::class,
            DataService\SubmissionLegislation::class => DataService\AbstractPublicInquiryDataFactory::class,
        ]
    ],
    'form_service_manager' => [
        'abstract_factories' => [AbstractLvaFormFactory::class],
        'aliases' => AbstractLvaFormFactory::FORM_SERVICE_CLASS_ALIASES
    ],
    'service_api_mapping' => array(
        'endpoints' => array(
            'nr' => 'http://olcs-nr/',
        )
    ),
    'hostnames' => array(),
    'marker_plugins' => array(
        'invokables' => array(
            Marker\ContinuationDetailMarker::class => Marker\ContinuationDetailMarker::class,
            Marker\LicenceStatusMarker::class => Marker\LicenceStatusMarker::class,
            Marker\LicenceStatusRuleMarker::class => Marker\LicenceStatusRuleMarker::class,
            Marker\DisqualificationMarker::class => Marker\DisqualificationMarker::class,
            Marker\CaseAppealMarker::class => Marker\CaseAppealMarker::class,
            Marker\CaseStayMarker::class => Marker\CaseStayMarker::class,
            Marker\BusRegShortNoticeRefused::class => Marker\BusRegShortNoticeRefused::class,
            Marker\BusRegEbsrMarker::class => Marker\BusRegEbsrMarker::class,
            Marker\TransportManager\SiQualificationMarker::class =>
                Marker\TransportManager\SiQualificationMarker::class,
            Marker\TransportManager\Rule450Marker::class => Marker\TransportManager\Rule450Marker::class,
            Marker\TransportManager\IsRemovedMarker::class => Marker\TransportManager\IsRemovedMarker::class,
            Marker\SoleTraderDisqualificationMarker::class => Marker\SoleTraderDisqualificationMarker::class,
        ),
    ),
    'date_settings' => array(
        'date_format' => 'd/m/Y',
        'datetime_format' => 'd/m/Y H:i',
        'datetimesec_format' => 'd/m/Y H:i:s'
    ),
    'webdav' => [
        'private_key' => 'LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlKSndJQkFBS0NBZ0VBd1REWDlyMXZVWC9PazZ6NER5NzkrdnlhVzNzL21pUkxCTlE3WmFmc2tRUG12WUR3CkN0dmN2ZUpybW42b1Q4VGxpN3gzWHc3c29yaTlTSmF3QXU3cHpXSnFZRFZsbnZ5TlF4aWJVblNnMVRyeElKTkcKNXpaa2d4ZW02SnVhYVhtSURlUDBQbmpBdGlTVUE5c1ZnSHFtbjhzK000RkVmODZ0V3M2QysrVCthS0oxb0xlTAp2QTVXVGF0NkU0ekIrczR6QkFiMmFEYXBqQkNrckRXNzU0cEV1d3hNckEyZlJic0JoMTlSWjl5aDUwbGV2OWRaCkJSaDltQmZLbEV0eDF4VUZ6bFY5NlJrYUxFVHZjQUIwMHhzL2lIU3I3V2VSOUJYTnMxZjNra2xndWhyYmFET1IKb012bUNLazhUTC9MZm9pMHZLSFNhUHpMK1R3cGNwVWIwYnZoZzJGTFRKUFRzbFJJQ09WZFpxZDVqUzU3WWsrVwpaMVJpeXFzSHhoS2pjWXBVL3pVYkNiaVZENVk5N3dNd0huQkFJQ0hSQ0JrdVU2U2VQYVIxcjdUNis4b0ZQNE9QCjIzOXdJSzZ2YXFnWDJjd3EzNHRaNlp6L0U3ZkcwY3dJU2E3K2pXTGppUHYyV0sxUHpBRmFVUUJxcWJuOUZxa2YKSmZuMjQzczFoMWo5V0F2dmNXc1hDTkdkUm4yMUtZWmVqb25KNk5yQ0Nxdm5qbHcydDE0VjJITG1UcmRkVEVpeQpLaDFJMGd0Z2wzL0swN011SjFoWlowR2RuUEVtdU9ab3NpZDd6QlQ1M3JPRnE3a01xQnpsMFJiUHBsci9XOGI2ClZ1OHZiVXUwVEhjN3R1c1FuTVB6ejJUNkE1RCs2Rk9maVVTcktKOEpPTEl4M2Q5aEk2VjU5b2tkVE5NQ0F3RUEKQVFLQ0FnQkI3QVBWbTBDUUE5ZWV4cWdDcmx0V09Mb0hPMkF4bmU3SFlCQkFtUE45YkdKaENjMWZOelQweW4xRApRN0wxUFUvQ3hmWEp4eEx5VjYyblJsd2JOQ0V0eDBaYk8vMUlLZytkOUppVG8xNTZSRm1oYndBRHg2aTJudXlDCmRRNVVyWGJDbnFWcVo5UUNreXE2d2hodE5lMERtOXZHd1haNVVqSVBTV2FpdzdvWVJFOTFId3ljaUJ2azl2MTYKREU3bzRWSEJMd3NIOXBjV1IxdVpzK0JCbXdubTljUjM5VklDL2xRQU9JTlR1Sks2bEd1emRLMVlzc213aTNYQgo4cGlPOXdwN0pPc0pEbHJDL21iaHhoWVhMellYdnhBbnUyNnZabjFCbEdQVFZCeWdoS2VYdi9rU2NHRTNWV0JSCkpDVXhNVDdUR3pqc1FFN3Q1aHlTajlUbFZZSVd0UGRlcmRiS3Y3YWw2ZzdINzFMSkZsNHVQS3RFUHFvbElpWWQKOUpMajU4QWtnVk56Um81ZU5YMDRUWDBvYkNXSzBiLzNHWWVxVDByRzY3UkVTRW5mOU1QNWFmQUsxRTl2cHEzNgo5bjNsRHpjU3FEc0tjRVNxeW9EVGQ5NkVpamNYeEd6dmZxVGw5bUNCYzFRWDA0YzZLUGtQZWJLQlFRbHJxT2JqCmMrclB5ZGZkTEF1eU16R2l6TzQxM3lJd2FQVjRYTTVBQmpTMGNGREFlZWpQRkRpV2doVUoxcFpzQ2UzRXNiWmIKYTlzcGV6YnRZUXZmVFNoVGNxd0J4VmU3MjBKelo5RkllOFh6YVh3WmhhU0RvRXdyWGJUeUEwOWtXMFBxRGk0ZQpmUXBBcTRJTitmQ3BsSlJkcEpoN09vTWJmd002WTgwVU12SkNXUVlmOWlBdE9tRmp1UUtDQVFFQStJbUdUZE1XCncwK2JRSmU5ZGowUW00Sng0RWtwcjRLTk1GUmF0TFFOcEJHUXU5dlltZld0L25sZXJiai9wZmdCZzZIS1BMUEQKTlpGNkJWaGhYeHJRanNpSG00a3hYQ1l4TFFWNWI5TzdHS3VLbWRvVGFmUGFoSUQ2VEtpNkZMRFR3LzRNTEU5UAoxUkxQekFSSmJHZ2didVhGT0RHYUZvMWI4ZFN2SzNWOVFXaDhEM3pFbjVwcGIxV1lvNENXbjBTUm5ocjFzdUsxClBnclBNUzFubWlCRXZWQisycGsxVTZ2QndKbVRtY2NXRUxTbmRTM2ZFeWNaZ014RkRNR0l1RTZMM0xFY2x2cjcKU3kzN09HSkRRaUppeVJoa1BzT0lGKzh5SGJRc01GSFR6VkpuTEw3R1p1VWJjMTlSMkJJelo2VFRXcGFzM3JaYwpWN25nVy9UOEZlcWZmd0tDQVFFQXh2M2d1ZWNZa1NZblh2VVpid1dDMkhlTmg3OXVMcUZsVXhubFk2QlVGVW9DCnl4THQxTTFHNEVWeElhQTN2Zis4MFpqK3hMMUlMWU1WTnJNcVJRQVIrSTV3bk1tNGk5TDhPU3ZSYWMwTVlrbVcKWExzNjQweXJBbG5GcldSdTBmb1hDYUxhU04ySGNIQkZtWndaRFNXRERWb3diZXJlUi9XTUN4SnMxa2xTQ0RMZwpZeGM4Vi9rUWhIei83RDNxUEJMM0h1ZTBkQ3lOVXVocU8rOXNoTytoZWZzVktnejVsMnNsUHpjQklESUt2WmJKCmNCQVF3dE9ZNy9FVnVac1BRUVJhVkJmOUVMd0RwYnBJbzNnSTU1L3VkWmJLdnlKc2Y5dVV6UW5RZjJqdlE4TmoKdG0yM2swUU9hMStOZVRseXU0aFdabnlrazBTdVBaSkJ3d1Y3MHJKOHJRS0NBUUJEODJXeTFXbTkwSUFEOHZpcgo0Um92U2tUVUsyeW9QYXRZY1ZlelhCNzJvbzdOcmRmVWtDVVlGQnJjcUYzTkJMZDFROERGUStpMU5xY0QyeHdVCklvS3U1d2ljYjYvOUg3d0dNUjc0Z2d6L083ZFRSUnBWdDRRaEFocHM1eHlwRjRkdWFJRHZoR2V4Tzhsd1lDT3EKN2ZVZ0hOWUUvUnJCMjdndHNCYU1iVHpucXlkd0hJNnRqRXdUVW5XL0RpTWdQR0VMdHhkQjUxWGlOcFpiUGF5NwpxT0xpVjZXM0luZy8vZytsRnRnU0RTcHRPdGNsNUhxL0E4dW5PVElQd01JZWtlc1BWYVVaYWxsV1BxWVd6bGJSCk9CR0dKWk1TemViaGxGWkJaTWRJRnJjdnhiM0xzQVVTa01VbWtBTVNiamQ3dU1iSVY3Vy8xbC84NUNjQlBVUEcKd2pVRkFvSUJBRzg5N0F4aEdZWERPNDFGSGJQSDgrN0pYdEI5ZnI0SXNkazBCOTJhK29ad09vR0dFbmk5VFJzQgpGdzZDUDhjeW1UN2U5Y1hNUHZaYWFsaUs3bDFtZmFWakU2ZEN5YTA1QkpGOVluTndFclU3aUJoTS9zMmt4WkRwCjJLMW5FT0RIbTJ0aW8vN0tBUFlsZlhNekpYb0k5MnRXZU81cHQxdW85R0lZS2NuZGNVTnVGYXl2aDZkeWIvNXcKMXEwZHE3MXJxTVNaS1hNc21OQzVadkljbGFEM1NXRWtzUjh4NDdIM1R2bzQ2S016OW5jb1BYSXRPUUdCUXVVWAo5Rm92U2ViQjVURlB1OFJJSnczVnh0ZkR1YzZxeEtidDVtZlZlYXc4ZDhIcjg2ZldaTE9RSGtVVXJ1UmZ6bVBPCkpndVh4d0Q1WmJ1amdHbG5vclIrOTg1cldWNWZNMzBDZ2dFQVh1UmFWbHJ6SXNUZXFuaU9DMEExd0YveHQ5ZjYKNkY5V2JiWE9WZHBXYkF4Z2lRVTJCTkJBdHBXUDV6T1I0WTdONGtxK3NyT0Z6K2gyUFc4dFQ5SmhCQ3ZHQXBSRQpZckpIZ1FNeEtEUy9NeXVDNkFZemttb2RocXBMbWdWR0YyOHh6QjhPWG5QeWd5ODIyYWhDa29TSWdlMTd2OE14CkFUQzExZEQ1anAxNVQ2LzhFOVRTYkEvckNiUzFpMnE2WlJEWndlbVBWMkxoMCt5MGxlbnpWbVdwcHdKVG9VLzEKa1FyS0svYUk4ZWdWMjNTQnF5T21HMWxsNGZCcURZcU1RT2N1akFOZGNSUGQ1dlVQZmtubWd3NzArNkt1MFEvKwozMkNOM093MjJjR0dxK0lkYkd6WUJPNnFneWJDNXNZRy95a2NwRDdzNzhnOGc2eVZ4bVdESVVqMTJ3PT0KLS0tLS1FTkQgUlNBIFBSSVZBVEUgS0VZLS0tLS0=',
        'default_lifetime_seconds' => 3000000,
        'url_pattern' => 'ms-word:ofe|u|https://iuweb.da.olcs.dev-dvsacloud.uk/documents-dav/%%s/olcs/%%s'
    ],
);
