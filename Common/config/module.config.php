<?php

use Common\Auth\Adapter\CommandAdapter;
use Common\Auth\Adapter\CommandAdapterFactory;
use Common\Auth\Service\AuthenticationServiceFactory;
use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Continuation as ContinuationControllers;
use Common\Controller\Factory\Continuation\ChecklistControllerFactory;
use Common\Controller\Factory\Continuation\ConditionsUndertakingsControllerFactory;
use Common\Controller\Factory\Continuation\DeclarationControllerFactory;
use Common\Controller\Factory\Continuation\FinancesControllerFactory;
use Common\Controller\Factory\Continuation\InsufficientFinancesControllerFactory;
use Common\Controller\Factory\Continuation\OtherFinancesControllerFactory;
use Common\Controller\Factory\Continuation\PaymentControllerFactory;
use Common\Controller\Factory\Continuation\ReviewControllerFactory;
use Common\Controller\Factory\Continuation\StartControllerFactory;
use Common\Controller\Factory\Continuation\SuccessControllerFactory;
use Common\Controller\Lva\ReviewController;
use Common\Data\Mapper\Licence\Surrender as SurrenderMapper;
use Common\Data\Mapper\Permits as PermitsMapper;
use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicMultiCheckboxFactory;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicRadioFactory;
use Common\Form\Element\DynamicRadioHtml;
use Common\Form\Element\DynamicRadioHtmlFactory;
use Common\Form\Element\DynamicSelect;
use Common\Form\Element\DynamicSelectFactory;
use Common\Form\Elements\Custom\OlcsCheckbox;
use Common\Form\Elements\Validators\TableRequiredValidator;
use Common\Form\View\Helper\FormRadio;
use Common\Form\View\Helper\FormInputSearch;
use Common\Form\View\Helper\Readonly as ReadonlyFormHelper;
use Common\FormService\Form\Continuation\ConditionsUndertakings;
use Common\FormService\Form\Continuation\ConditionsUndertakingsFactory;
use Common\FormService\Form\Continuation\Declaration;
use Common\FormService\Form\Continuation\DeclarationFactory;
use Common\FormService\FormServiceAbstractFactory;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Data as DataService;
use Common\Service\Data\Search\SearchType;
use Common\Service\Helper as HelperService;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Qa as QaService;
use Common\Service\Review\LicenceConditionsUndertakingsReviewService;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Translator\TranslationLoader;
use Common\Service\Translator\TranslationLoaderFactory;
use Common\View\Helper\Panel;
use Laminas\Form\Element\Textarea;
use Laminas\ServiceManager\Factory\InvokableFactory;
use LmcRbacMvc\Identity\IdentityProviderInterface;

$release = json_decode(file_get_contents(__DIR__ . '/release.json'), true);

return [
    'router' => [
        'routes' => array_merge(
            require(__DIR__ . '/routes/general.php'),
            require(__DIR__ . '/routes/continuations.php')
        )
    ],
    'controllers' => [
        // @NOTE These delegators can live in common as both internal and external app controllers currently use the
        // same adapter. Self Serve registers these itself within the application module.
        'invokables' => [
            \Common\Controller\FileController::class => \Common\Controller\FileController::class,
            Common\Controller\TransportManagerReviewController::class =>
                Common\Controller\TransportManagerReviewController::class,
            \Common\Controller\ErrorController::class => \Common\Controller\ErrorController::class,
            \Common\Controller\GuidesController::class => \Common\Controller\GuidesController::class,
        ],
        'factories' => [
            ContinuationControllers\ChecklistController::class => ChecklistControllerFactory::class,
            ContinuationControllers\ConditionsUndertakingsController::class => ConditionsUndertakingsControllerFactory::class,
            ContinuationControllers\DeclarationController::class => DeclarationControllerFactory::class,
            ContinuationControllers\FinancesController::class => FinancesControllerFactory::class,
            ContinuationControllers\InsufficientFinancesController::class => InsufficientFinancesControllerFactory::class,
            ContinuationControllers\OtherFinancesController::class => OtherFinancesControllerFactory::class,
            ContinuationControllers\PaymentController::class => PaymentControllerFactory::class,
            ContinuationControllers\ReviewController::class => ReviewControllerFactory::class,
            ContinuationControllers\StartController::class => StartControllerFactory::class,
            ContinuationControllers\SuccessController::class => SuccessControllerFactory::class,
            ReviewController::class => Common\Controller\Lva\Factories\Controller\ReviewControllerFactory::class,
            \Common\Controller\Lva\Schedule41Controller::class => \Common\Controller\Lva\Factories\Controller\Schedule41ControllerFactory::class,
        ],
        'aliases' => [
            'Common\Controller\File' => \Common\Controller\FileController::class,
            'ContinuationController/Start' => ContinuationControllers\StartController::class,
            'ContinuationController/Checklist' => ContinuationControllers\ChecklistController::class,
            'ContinuationController/ConditionsUndertakings' => ContinuationControllers\ConditionsUndertakingsController::class,
            'ContinuationController/Finances' => ContinuationControllers\FinancesController::class,
            'ContinuationController/OtherFinances' => ContinuationControllers\OtherFinancesController::class,
            'ContinuationController/InsufficientFinances' => ContinuationControllers\InsufficientFinancesController::class,
            'ContinuationController/Declaration' => ContinuationControllers\DeclarationController::class,
            'ContinuationController/Payment' => ContinuationControllers\PaymentController::class,
            'ContinuationController/Success' => ContinuationControllers\SuccessController::class,
            'ContinuationController/Review' => ContinuationControllers\ReviewController::class,
        ]
    ],
    'controller_plugins' => [
        'invokables' => [
            'redirect' => \Common\Controller\Plugin\Redirect::class,
            \Common\Controller\Plugin\Redirect::class => \Common\Controller\Plugin\Redirect::class,
        ],
        'factories' => [
            'currentUser' => \Common\Controller\Plugin\CurrentUserFactory::class,
            \Common\Controller\Plugin\CurrentUser::class => \Common\Controller\Plugin\CurrentUserFactory::class,
            'ElasticSearch' => \Common\Controller\Plugin\ElasticSearchFactory::class,
            'handleQuery' => \Common\Controller\Plugin\HandleQueryFactory::class,
            \Common\Controller\Plugin\HandleQuery::class => \Common\Controller\Plugin\HandleQueryFactory::class,
            'handleCommand' => \Common\Controller\Plugin\HandleCommandFactory::class,
            \Common\Controller\Plugin\HandleCommand::class => \Common\Controller\Plugin\HandleCommandFactory::class,
            'featuresEnabled' => \Common\Controller\Plugin\FeaturesEnabledFactory::class,
            'featuresEnabledForMethod' => \Common\Controller\Plugin\FeaturesEnabledForMethodFactory::class,
        ]
    ],
    'console' => [
        'router' => [
            'routes' => [
                'route101' => [
                    'options' => [
                        'route' => 'formrewrite [olcs|common|selfserve]:formnamespace',
                        'defaults' => [
                            'controller' => 'Common\Controller\FormRewrite',
                            'action' => 'index'
                        ]
                    ]
                ],
                'route102' => [
                    'options' => [
                        'route' => 'formcleanup [olcs|common|selfserve]:formnamespace',
                        'defaults' => [
                            'controller' => 'Common\Controller\FormRewrite',
                            'action' => 'cleanup'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'version' => ($release['version'] ?? ''),
    'service_manager' => [
        'shared' => [
            'Helper\FileUpload' => false,
            // Create a new request each time
            'CqrsRequest' => false
        ],
        'abstract_factories' => [
            \Common\Util\AbstractServiceFactory::class
        ],
        'aliases' => [
            'Cache' => \Laminas\Cache\Storage\StorageInterface::class,
            'DataServiceManager' => \Common\Service\Data\PluginManager::class,
            'translator' => 'MvcTranslator',
            'TableBuilder' => \Common\Service\Table\TableBuilderFactory::class,
            'NavigationFactory' => \Common\Service\NavigationFactory::class,
            'QueryService' => \Common\Service\Cqrs\Query\CachingQueryService::class,
            'CommandService' => \Common\Service\Cqrs\Command\CommandService::class,
            'CommandSender' => CommandSender::class,
            'Review\ConditionsUndertakings' => Common\Service\Review\ConditionsUndertakingsReviewService::class,
            'Data\Address' => DataService\AddressDataService::class,
            'Script' => \Common\Service\Script\ScriptFactory::class,

            'Helper\FlashMessenger' => HelperService\FlashMessengerHelperService::class,
            'Helper\Form' => HelperService\FormHelperService::class,
            'Helper\Guidance' => HelperService\GuidanceHelperService::class,
            'Helper\Translation' => HelperService\TranslationHelperService::class,
            'Helper\TransportManager' => HelperService\TransportManagerHelperService::class,
            'Helper\Url' => HelperService\UrlHelperService::class,
            'Lva\People' => Common\Service\Lva\PeopleLvaService::class,
            'Lva\Variation' => Common\Service\Lva\VariationLvaService::class,
            'LanguagePreference' => \Common\Preference\Language::class,
            'QaCommonHtmlAdder' => QaService\Custom\Common\HtmlAdder::class,

            // Controller LVA Adapters
            'ApplicationConditionsUndertakingsAdapter' => \Common\Controller\Lva\Adapters\ApplicationConditionsUndertakingsAdapter::class,
            'ApplicationFinancialEvidenceAdapter' => Common\Controller\Lva\Adapters\ApplicationFinancialEvidenceAdapter::class,
            'ApplicationLvaAdapter' => \Common\Controller\Lva\Adapters\ApplicationLvaAdapter::class,
            'ApplicationPeopleAdapter' => Common\Controller\Lva\Adapters\ApplicationPeopleAdapter::class,
            'ApplicationTransportManagerAdapter' => Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter::class,
            'GenericBusinessTypeAdapter' => \Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter::class,
            'LicenceConditionsUndertakingsAdapter' => Common\Controller\Lva\Adapters\LicenceConditionsUndertakingsAdapter::class,
            'LicenceLvaAdapter' => \Common\Controller\Lva\Adapters\LicenceLvaAdapter::class,
            'LicencePeopleAdapter' => Common\Controller\Lva\Adapters\LicencePeopleAdapter::class,
            'LicenceTransportManagerAdapter' => Common\Controller\Lva\Adapters\LicenceTransportManagerAdapter::class,
            'VariationConditionsUndertakingsAdapter' => Common\Controller\Lva\Adapters\VariationConditionsUndertakingsAdapter::class,
            'VariationFinancialEvidenceAdapter' => Common\Controller\Lva\Adapters\VariationFinancialEvidenceAdapter::class,
            'VariationLvaAdapter' => \Common\Controller\Lva\Adapters\VariationLvaAdapter::class,
            'VariationPeopleAdapter' => Common\Controller\Lva\Adapters\VariationPeopleAdapter::class,
            'VariationTransportManagerAdapter' => Common\Controller\Lva\Adapters\VariationTransportManagerAdapter::class,

            'DataMapper\DashboardTmApplications' => \Common\Service\Table\DataMapper\DashboardTmApplications::class,


            'FormServiceManager' => Common\FormService\FormServiceManager::class,
            'Review\LicenceConditionsUndertakings' => Common\Service\Review\LicenceConditionsUndertakingsReviewService::class,
            'QuerySender' => \Common\Service\Cqrs\Query\QuerySender::class,
        ],
        'invokables' => [
            \Common\Service\NavigationFactory::class => \Common\Service\NavigationFactory::class,
            'SectionConfig' => \Common\Service\Data\SectionConfig::class,
            \Common\Filesystem\Filesystem::class => \Common\Filesystem\Filesystem::class,
            'VehicleList' => '\Common\Service\VehicleList\VehicleList',
            'postcode' => 'Common\Service\Postcode\Postcode',
            'CompaniesHouseApi' => 'Common\Service\CompaniesHouse\Api',
            'TableRequired' => \Common\Form\Elements\Validators\TableRequiredValidator::class,
            \Common\Service\Table\DataMapper\DashboardTmApplications::class => \Common\Service\Table\DataMapper\DashboardTmApplications::class,

            'applicationIdValidator' => 'Common\Form\Elements\Validators\ApplicationIdValidator',
            'totalVehicleAuthorityValidator' => 'Common\Form\Elements\Validators\Lva\TotalVehicleAuthorityValidator',
            'section.vehicle-safety.vehicle.formatter.vrm' =>
                \Common\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm::class,
            'Common\Rbac\UserProvider' => 'Common\Rbac\UserProvider',
            'QaCheckboxFactory' => QaService\CheckboxFactory::class,
            'QaTextFactory' => QaService\TextFactory::class,
            'QaRadioFactory' => QaService\RadioFactory::class,
            'QaFieldsetFactory' => QaService\FieldsetFactory::class,
            'QaValidatorsAdder' => QaService\ValidatorsAdder::class,
            'QaEcmtYesNoRadioFactory' => QaService\Custom\Ecmt\YesNoRadioFactory::class,
            'QaEcmtRestrictedCountriesMultiCheckboxFactory'
                => QaService\Custom\Ecmt\RestrictedCountriesMultiCheckboxFactory::class,
            'QaEcmtInternationalJourneysIsValidHandler' =>
                QaService\Custom\Ecmt\InternationalJourneysIsValidHandler::class,
            'QaEcmtAnnualTripsAbroadIsValidHandler' =>
                QaService\Custom\Ecmt\AnnualTripsAbroadIsValidHandler::class,
            'QaBilateralYesNoValueOptionsGenerator' =>
                QaService\Custom\Bilateral\YesNoValueOptionsGenerator::class,
            'QaBilateralStandardAndCabotageYesNoRadioFactory' =>
                QaService\Custom\Bilateral\StandardAndCabotageYesNoRadioFactory::class,
            'QaBilateralRadioFactory' =>
                QaService\Custom\Bilateral\RadioFactory::class,
            'QaBilateralYesNoRadioOptionsApplier' => QaService\Custom\Bilateral\YesNoRadioOptionsApplier::class,

            'QaBilateralNoOfPermitsFieldsetPopulator' =>
                QaService\Custom\Bilateral\NoOfPermitsFieldsetPopulator::class,
            'QaBilateralNoOfPermitsMoroccoFieldsetPopulator' =>
                QaService\Custom\Bilateral\NoOfPermitsMoroccoFieldsetPopulator::class,
            'QaBilateralPermitUsageIsValidHandler' => QaService\Custom\Bilateral\PermitUsageIsValidHandler::class,
            'QaBilateralStandardAndCabotageSubmittedAnswerGenerator' =>
                QaService\Custom\Bilateral\StandardAndCabotageSubmittedAnswerGenerator::class,
            'QaDateTimeFactory' => QaService\DateTimeFactory::class,

            'QaRoadWorthinessMakeAndModelFieldsetModifier' =>
                QaService\FieldsetModifier\RoadworthinessMakeAndModelFieldsetModifier::class,

            'QaEcmtNoOfPermitsSingleDataTransformer' =>
                QaService\DataTransformer\EcmtNoOfPermitsSingleDataTransformer::class,
            QaService\Custom\Common\HtmlAdder::class => QaService\Custom\Common\HtmlAdder::class,

            Common\Data\Mapper\DefaultMapper::class => Common\Data\Mapper\DefaultMapper::class,
            SurrenderMapper\OperatorLicence::class => SurrenderMapper\OperatorLicence::class,
            SurrenderMapper\CommunityLicence::class => SurrenderMapper\CommunityLicence::class,
            \Common\Service\Helper\ResponseHelperService::class => \Common\Service\Helper\ResponseHelperService::class,
            \Common\Form\FormValidator::class => \Common\Form\FormValidator::class,

            'Zend\Authentication\AuthenticationService' => \Laminas\Authentication\AuthenticationService::class,
            DataService\UserTypesListDataService::class => DataService\UserTypesListDataService::class,
            HelperService\OppositionHelperService::class => HelperService\OppositionHelperService::class,
            HelperService\RestrictionHelperService::class => HelperService\RestrictionHelperService::class,
            StringHelperService::class => StringHelperService::class,
            DataHelperService::class => DataHelperService::class,
            \Laminas\View\HelperPluginManager::class => \Laminas\View\HelperPluginManager::class,
            HelperService\DateHelperService::class => HelperService\DateHelperService::class,
            HelperService\ComplaintsHelperService::class => HelperService\ComplaintsHelperService::class,
            \Laminas\Router\Http\TreeRouteStack::class => \Laminas\Router\Http\TreeRouteStack::class,
        ],
        'factories' => [
            DataService\AbstractDataServiceServices::class => DataService\AbstractDataServiceServicesFactory::class,
            DataService\AbstractListDataServiceServices::class => DataService\AbstractListDataServiceServicesFactory::class,
            DataService\AddressDataService::class => DataService\AbstractDataServiceFactory::class,
            DataService\Application::class => DataService\AbstractDataServiceFactory::class,
            DataService\ApplicationPathGroup::class => DataService\AbstractDataServiceFactory::class,
            DataService\BusRegBrowseListDataService::class => DataService\AbstractDataServiceFactory::class,
            DataService\BusRegSearchViewListDataService::class => DataService\AbstractDataServiceFactory::class,
            'category' => DataService\CategoryDataService::class,
            DataService\ContactDetails::class => DataService\AbstractListDataServiceFactory::class,
            DataService\Country::class => DataService\AbstractDataServiceFactory::class,
            'country' => DataService\AbstractDataServiceFactory::class,
            DataService\FeeType::class => DataService\AbstractDataServiceFactory::class,
            DataService\FeeTypeDataService::class => DataService\AbstractDataServiceFactory::class,
            DataService\IrhpPermitType::class => DataService\AbstractDataServiceFactory::class,
            DataService\Licence::class => DataService\AbstractDataServiceFactory::class,
            DataService\LocalAuthority::class => DataService\AbstractDataServiceFactory::class,
            DataService\RefData::class => DataService\RefDataFactory::class,
            DataService\RefDataServices::class => DataService\RefDataServicesFactory::class,
            DataService\Role::class => DataService\AbstractDataServiceFactory::class,
            DataService\SiCategoryType::class => DataService\AbstractDataServiceFactory::class,
            'staticList' => DataService\StaticListFactory::class,
            DataService\Surrender::class => DataService\AbstractDataServiceFactory::class,
            DataService\TrafficArea::class => DataService\AbstractDataServiceFactory::class,

            HelperService\FileUploadHelperService::class => HelperService\FileUploadHelperServiceFactory::class,
            HelperService\FlashMessengerHelperService::class => HelperService\FlashMessengerHelperServiceFactory::class,
            HelperService\FormHelperService::class => HelperService\FormHelperServiceFactory::class,
            HelperService\GuidanceHelperService::class => HelperService\GuidanceHelperServiceFactory::class,
            HelperService\TranslationHelperService::class => HelperService\TranslationHelperServiceFactory::class,
            HelperService\TransportManagerHelperService::class => HelperService\TransportManagerHelperServiceFactory::class,
            HelperService\UrlHelperService::class => HelperService\UrlHelperServiceFactory::class,

            Common\Service\Lva\PeopleLvaService::class => Common\Service\Lva\PeopleLvaServiceFactory::class,
            Common\Service\Lva\VariationLvaService::class => Common\Service\Lva\VariationLvaServiceFactory::class,

            CommandSender::class => CommandSender::class,
            QuerySender::class => \Common\Service\Cqrs\Query\QuerySender::class,

            \Common\Preference\Language::class => \Common\Preference\Language::class,
            'LanguageListener' => \Common\Preference\LanguageListener::class,
            'CqrsRequest' => \Common\Service\Cqrs\RequestFactory::class,
            \Common\Service\Cqrs\Query\CachingQueryService::class
                => \Common\Service\Cqrs\Query\CachingQueryServiceFactory::class,
            \Common\Service\Cqrs\Query\QueryService::class => \Common\Service\Cqrs\Query\QueryServiceFactory::class,
            \Common\Service\Cqrs\Command\CommandService::class => \Common\Service\Cqrs\Command\CommandServiceFactory::class,
            \Common\Service\Script\ScriptFactory::class => \Common\Service\Script\ScriptFactory::class,
            FormServiceManager::class => Common\FormService\FormServiceManagerFactory::class,
            'Table' => \Common\Service\Table\TableFactory::class,
            \Common\Service\Table\TableFactory::class => \Common\Service\Table\TableFactory::class,
            // Added in a true Laminas Framework V2 compatible factory for TableBuilder, eventually to replace Table above.
            \Common\Service\Table\TableBuilderFactory::class => \Common\Service\Table\TableBuilderFactory::class,
            'ServiceApiResolver' => \Common\Service\Api\ResolverFactory::class,
            'SectionService' => '\Common\Controller\Service\SectionServiceFactory',
            'FormAnnotationBuilder' => \Common\Service\FormAnnotationBuilderFactory::class,
            \Common\Service\Data\PluginManager::class => Common\Service\Data\PluginManagerFactory::class,
            \Laminas\Cache\Storage\StorageInterface::class => \Laminas\Cache\Service\StorageCacheFactory::class,
            \Common\Rbac\Navigation\IsAllowedListener::class => Common\Rbac\Navigation\IsAllowedListener::class,
            \Common\Rbac\Service\Permission::class => \Common\Rbac\Service\PermissionFactory::class,
            \Common\Service\Data\Search\SearchTypeManager::class =>
                \Common\Service\Data\Search\SearchTypeManagerFactory::class,
            \Common\Rbac\JWTIdentityProvider::class => \Common\Rbac\JWTIdentityProviderFactory::class,
            \Common\Service\AntiVirus\Scan::class => \Common\Service\AntiVirus\Scan::class,
            'QaCommonWarningAdder' => QaService\Custom\Common\WarningAdderFactory::class,
            'QaCommonIsValidBasedWarningAdder' => QaService\Custom\Common\IsValidBasedWarningAdderFactory::class,
            'QaCommonFileUploadFieldsetGenerator' => QaService\Custom\Common\FileUploadFieldsetGeneratorFactory::class,
            'QaCheckboxFieldsetPopulator' => QaService\CheckboxFieldsetPopulatorFactory::class,
            'QaTextFieldsetPopulator' => QaService\TextFieldsetPopulatorFactory::class,
            'QaRadioFieldsetPopulator' => QaService\RadioFieldsetPopulatorFactory::class,
            'QaFieldsetAdder' => QaService\FieldsetAdderFactory::class,
            'QaFieldsetPopulator' => QaService\FieldsetPopulatorFactory::class,
            'QaFieldsetPopulatorProvider' => QaService\FieldsetPopulatorProviderFactory::class,
            'QaTranslateableTextHandler' => QaService\TranslateableTextHandlerFactory::class,
            'QaTranslateableTextParameterHandler' => QaService\TranslateableTextParameterHandlerFactory::class,
            'QaFormattedTranslateableTextParametersGenerator' =>
                QaService\FormattedTranslateableTextParametersGeneratorFactory::class,
            'QaEcmtNoOfPermitsEitherStrategySelectingFieldsetPopulator' =>
                QaService\Custom\Ecmt\NoOfPermitsEitherStrategySelectingFieldsetPopulatorFactory::class,
            'QaEcmtNoOfPermitsBothStrategySelectingFieldsetPopulator' =>
                QaService\Custom\Ecmt\NoOfPermitsBothStrategySelectingFieldsetPopulatorFactory::class,
            'QaEcmtNoOfPermitsSingleFieldsetPopulator' =>
                QaService\Custom\Ecmt\NoOfPermitsSingleFieldsetPopulatorFactory::class,
            'QaEcmtNoOfPermitsEitherFieldsetPopulator' =>
                QaService\Custom\Ecmt\NoOfPermitsEitherFieldsetPopulatorFactory::class,
            'QaEcmtNoOfPermitsBothFieldsetPopulator' =>
                QaService\Custom\Ecmt\NoOfPermitsBothFieldsetPopulatorFactory::class,
            'QaEcmtNoOfPermitsBaseInsetTextGenerator' =>
                QaService\Custom\Ecmt\NoOfPermitsBaseInsetTextGeneratorFactory::class,
            'QaEcmtPermitUsageFieldsetPopulator' =>
                QaService\Custom\Ecmt\PermitUsageFieldsetPopulatorFactory::class,
            'QaEcmtCheckEcmtNeededFieldsetPopulator' =>
                QaService\Custom\Ecmt\CheckEcmtNeededFieldsetPopulatorFactory::class,
            'QaEcmtRestrictedCountriesFieldsetPopulator' =>
                QaService\Custom\Ecmt\RestrictedCountriesFieldsetPopulatorFactory::class,
            'QaEcmtAnnualTripsAbroadFieldsetPopulator' =>
                QaService\Custom\Ecmt\AnnualTripsAbroadFieldsetPopulatorFactory::class,
            'QaEcmtInternationalJourneysFieldsetPopulator' =>
                QaService\Custom\Ecmt\InternationalJourneysFieldsetPopulatorFactory::class,
            'QaEcmtShortTermEarliestPermitDateFieldsetPopulator' =>
                QaService\Custom\EcmtShortTerm\EarliestPermitDateFieldsetPopulatorFactory::class,
            'QaEcmtRemovalPermitStartDateFieldsetPopulator' =>
                QaService\Custom\EcmtRemoval\PermitStartDateFieldsetPopulatorFactory::class,
            'QaEcmtNiWarningConditionalAdder' =>
                QaService\Custom\Ecmt\NiWarningConditionalAdderFactory::class,
            'QaEcmtInternationalJourneysDataHandler' =>
                QaService\Custom\Ecmt\InternationalJourneysDataHandlerFactory::class,
            'QaEcmtAnnualTripsAbroadDataHandler' =>
                QaService\Custom\Ecmt\AnnualTripsAbroadDataHandlerFactory::class,
            'QaEcmtSectorsFieldsetPopulator' =>
                QaService\Custom\Ecmt\SectorsFieldsetPopulatorFactory::class,
            'QaEcmtInfoIconAdder' =>
                QaService\Custom\Ecmt\InfoIconAdderFactory::class,
            'QaCertRoadworthinessMotExpiryDateFieldsetPopulator' =>
                QaService\Custom\CertRoadworthiness\MotExpiryDateFieldsetPopulatorFactory::class,
            'QaBilateralStandardYesNoValueOptionsGenerator' =>
                QaService\Custom\Bilateral\StandardYesNoValueOptionsGeneratorFactory::class,
            'QaBilateralYesNoWithMarkupForNoPopulator' =>
                QaService\Custom\Bilateral\YesNoWithMarkupForNoPopulatorFactory::class,
            'QaBilateralPermitUsageFieldsetPopulator' =>
                QaService\Custom\Bilateral\PermitUsageFieldsetPopulatorFactory::class,
            'QaBilateralCabotageOnlyFieldsetPopulator' =>
                QaService\Custom\Bilateral\CabotageOnlyFieldsetPopulatorFactory::class,
            'QaBilateralStandardAndCabotageFieldsetPopulator' =>
                QaService\Custom\Bilateral\StandardAndCabotageFieldsetPopulatorFactory::class,
            'QaBilateralThirdCountryFieldsetPopulator' =>
                QaService\Custom\Bilateral\ThirdCountryFieldsetPopulatorFactory::class,
            'QaBilateralEmissionsStandardsFieldsetPopulator' =>
                QaService\Custom\Bilateral\EmissionsStandardsFieldsetPopulatorFactory::class,
            'QaBilateralPermitUsageDataHandler' => QaService\Custom\Bilateral\PermitUsageDataHandlerFactory::class,
            'QaBilateralStandardAndCabotageDataHandler' =>
                QaService\Custom\Bilateral\StandardAndCabotageDataHandlerFactory::class,
            'QaBilateralStandardAndCabotageIsValidHandler' =>
                QaService\Custom\Bilateral\StandardAndCabotageIsValidHandlerFactory::class,

            'QaFieldsetModifier' => QaService\FieldsetModifier\FieldsetModifierFactory::class,

            'QaApplicationStepsPostDataTransformer' =>
                QaService\DataTransformer\ApplicationStepsPostDataTransformerFactory::class,
            'QaDataTransformerProvider' => QaService\DataTransformer\DataTransformerProviderFactory::class,

            Common\Service\Review\AbstractReviewServiceServices::class
                => Common\Service\Review\AbstractReviewServiceServicesFactory::class,
            Common\Service\Review\ConditionsUndertakingsReviewService::class
                => Common\Service\Review\GenericFactory::class,
            'Review\LicenceConditionsUndertakings'
                => Common\Service\Review\LicenceConditionsUndertakingsReviewServiceFactory::class,

            PermitsMapper\NoOfPermits::class => PermitsMapper\NoOfPermitsFactory::class,
            Common\Service\User\LastLoginService::class => Common\Service\User\LastLoginServiceFactory::class,
            'HtmlPurifier' => \Common\Service\Utility\HtmlPurifierFactory::class,
           \Laminas\Form\View\Helper\FormLabel::class => \Common\Form\View\Helper\FormLabelFactory::class,
            \Common\Form\Elements\Validators\Messages\FormElementMessageFormatter::class => \Common\Form\Elements\Validators\Messages\FormElementMessageFormatterFactory::class,

            AuthenticationServiceInterface::class => AuthenticationServiceFactory::class,
            CommandAdapter::class => CommandAdapterFactory::class,
            \Laminas\Authentication\Storage\Session::class => \Common\Auth\SessionFactory::class,
            IdentityProviderInterface::class => \Common\Rbac\IdentityProviderFactory::class,
            \Common\Auth\Service\RefreshTokenService::class => \Common\Auth\Service\RefreshTokenServiceFactory::class,
            \Common\Data\Mapper\Lva\GoodsVehiclesVehicle::class => \Common\Data\Mapper\Lva\GoodsVehiclesVehicleFactory::class,

            // Controller LVA Adapters
            \Common\Controller\Lva\Adapters\ApplicationConditionsUndertakingsAdapter::class => \Common\Controller\Lva\Factories\Adapter\ApplicationConditionsUndertakingsAdapterFactory::class,
            \Common\Controller\Lva\Adapters\ApplicationFinancialEvidenceAdapter::class => \Common\Controller\Lva\Factories\Adapter\ApplicationFinancialEvidenceAdapterFactory::class,
            \Common\Controller\Lva\Adapters\ApplicationLvaAdapter::class => \Common\Controller\Lva\Factories\Adapter\ApplicationLvaAdapterFactory::class,
            \Common\Controller\Lva\Adapters\ApplicationPeopleAdapter::class => \Common\Controller\Lva\Factories\Adapter\ApplicationPeopleAdapterFactory::class,
            \Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter::class => \Common\Controller\Lva\Factories\Adapter\ApplicationTransportManagerAdapterFactory::class,
            \Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter::class => \Common\Controller\Lva\Factories\Adapter\GenericBusinessTypeAdapterFactory::class,
            \Common\Controller\Lva\Adapters\LicenceConditionsUndertakingsAdapter::class => \Common\Controller\Lva\Factories\Adapter\LicenceConditionsUndertakingsAdapterFactory::class,
            \Common\Controller\Lva\Adapters\LicenceLvaAdapter::class => \Common\Controller\Lva\Factories\Adapter\LicenceLvaAdapterFactory::class,
            \Common\Controller\Lva\Adapters\LicencePeopleAdapter::class => \Common\Controller\Lva\Factories\Adapter\LicencePeopleAdapterFactory::class,
            \Common\Controller\Lva\Adapters\LicenceTransportManagerAdapter::class => \Common\Controller\Lva\Factories\Adapter\LicenceTransportManagerAdapterFactory::class,
            \Common\Controller\Lva\Adapters\VariationConditionsUndertakingsAdapter::class => \Common\Controller\Lva\Factories\Adapter\VariationConditionsUndertakingsAdapterFactory::class,
            \Common\Controller\Lva\Adapters\VariationFinancialEvidenceAdapter::class => \Common\Controller\Lva\Factories\Adapter\VariationFinancialEvidenceAdapterFactory::class,
            \Common\Controller\Lva\Adapters\VariationLvaAdapter::class => \Common\Controller\Lva\Factories\Adapter\VariationLvaAdapterFactory::class,
            \Common\Controller\Lva\Adapters\VariationPeopleAdapter::class => \Common\Controller\Lva\Factories\Adapter\VariationPeopleAdapterFactory::class,
            \Common\Controller\Lva\Adapters\VariationTransportManagerAdapter::class => \Common\Controller\Lva\Factories\Adapter\VariationTransportManagerAdapterFactory::class,
            LicenceConditionsUndertakingsReviewService::class => Common\Service\Review\LicenceConditionsUndertakingsReviewServiceFactory::class,
            'MvcTranslator' => \Laminas\I18n\Translator\TranslatorServiceFactory::class,
            FormatterPluginManager::class => static function ($container): \Common\Service\Table\Formatter\FormatterPluginManager {
                $config = $container->get('config');
                $formatterConfig = $config['formatter_plugins'] ?? [];
                return new FormatterPluginManager($container, $formatterConfig);
            },
        ],
    ],
    'formatter_plugins' => include __DIR__ . '/formatter-plugins.config.php',
    'file_uploader' => [
        'default' => 'ContentStore',
        'config' => [
            'location' => 'documents',
            'defaultPath' => '[locale]/[doc_type_name]/[year]/[month]', // e.g. gb/publications/2015/03
        ]
    ],
    'navigation_helpers' =>  [
        'invokables' => [
            'menuRbac' => Common\View\Helper\Navigation\MenuRbac::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'formRadioOption' => \Common\Form\View\Helper\FormRadioOption::class,
            'formRadioHorizontal' => \Common\Form\View\Helper\FormRadioHorizontal::class,
            'formCheckboxAdvanced' => \Common\Form\View\Helper\FormCheckboxAdvanced::class,
            'formRadioVertical' => \Common\Form\View\Helper\FormRadioVertical::class,
            'form' => \Common\Form\View\Helper\Form::class,
            \Common\Form\View\Helper\FormCollection::class => Common\Form\View\Helper\FormCollection::class,
            'formdatetimeselect' => \Common\Form\View\Helper\FormDateTimeSelect::class,
            'formDateSelect' => \Common\Form\View\Helper\FormDateSelect::class,
            FormInputSearch::class => FormInputSearch::class,
            'formPlainText' => \Common\Form\View\Helper\FormPlainText::class,
            'addTags' => \Common\View\Helper\AddTags::class,
            'transportManagerApplicationStatus' => \Common\View\Helper\TransportManagerApplicationStatus::class,
            'status' => \Common\View\Helper\Status::class,
            'address' => \Common\View\Helper\Address::class,
            'personName' => \Common\View\Helper\PersonName::class,
            'dateTime' => \Common\View\Helper\DateTime::class,
            'returnToAddress' => Common\View\Helper\ReturnToAddress::class,
            'navigationParentPage' => Common\View\Helper\NavigationParentPage::class,
            'panel' => Panel::class,
            'link' => Common\View\Helper\Link::class,
            'linkNewWindow' => Common\View\Helper\LinkNewWindow::class,
            'linkNewWindowExternal' => Common\View\Helper\LinkNewWindowExternal::class,
            'linkModal' => Common\View\Helper\LinkModal::class,

            //  read only elements helpers
            ReadonlyFormHelper\FormFieldset::class => ReadonlyFormHelper\FormFieldset::class,
            ReadonlyFormHelper\FormFileUploadList::class => ReadonlyFormHelper\FormFileUploadList::class,
            'readonlyformitem' => ReadonlyFormHelper\FormItem::class,
            'readonlyformselect' => ReadonlyFormHelper\FormSelect::class,
            'readonlyformdateselect' => ReadonlyFormHelper\FormDateSelect::class,
            'readonlyformrow' => ReadonlyFormHelper\FormRow::class,
            'readonlyformtable' => ReadonlyFormHelper\FormTable::class,
            'readOnlyActions' => \Common\View\Helper\ReadOnlyActions::class,

            'currencyFormatter' => \Common\View\Helper\CurrencyFormatter::class,

            'formButton'              => \Laminas\Form\View\Helper\FormButton::class,
            'formCaptcha'             => \Laminas\Form\View\Helper\FormCaptcha::class,
            'formCheckbox'            => \Laminas\Form\View\Helper\FormCheckbox::class,
            'formColor'               => \Laminas\Form\View\Helper\FormColor::class,
            'formDate'                => \Laminas\Form\View\Helper\FormDate::class,
            'formDatetime'            => \Laminas\Form\View\Helper\FormDateTime::class,
            'formdatetimelocal'       => \Laminas\Form\View\Helper\FormDateTimeLocal::class,
            'formEmail'               => \Laminas\Form\View\Helper\FormEmail::class,
            'formFile'                => \Laminas\Form\View\Helper\FormFile::class,
            'formHidden'              => \Laminas\Form\View\Helper\FormHidden::class,
            'formImage'               => \Laminas\Form\View\Helper\FormImage::class,
            'formInput'               => \Laminas\Form\View\Helper\FormInput::class,
            'formLabel'               => \Laminas\Form\View\Helper\FormLabel::class,
            'formMonth'               => \Laminas\Form\View\Helper\FormMonth::class,
            'formmonthselect'         => \Laminas\Form\View\Helper\FormMonthSelect::class,
            'formmulticheckbox'       => \Laminas\Form\View\Helper\FormMultiCheckbox::class,
            'formNumber'              => \Laminas\Form\View\Helper\FormNumber::class,
            'formPassword'            => \Laminas\Form\View\Helper\FormPassword::class,
            'formRadio'               => \Common\Form\View\Helper\FormRadio::class,
            'formRange'               => \Laminas\Form\View\Helper\FormRange::class,
            'formReset'               => \Laminas\Form\View\Helper\FormReset::class,
            'formSearch'              => \Laminas\Form\View\Helper\FormSearch::class,
            'formSelect'              => \Laminas\Form\View\Helper\FormSelect::class,
            'formSubmit'              => \Laminas\Form\View\Helper\FormSubmit::class,
            'formTel'                 => \Laminas\Form\View\Helper\FormTel::class,
            'formText'                => \Laminas\Form\View\Helper\FormText::class,
            'formTextarea'            => \Laminas\Form\View\Helper\FormTextarea::class,
            'formTime'                => \Laminas\Form\View\Helper\FormTime::class,
            'formUrl'                 => \Laminas\Form\View\Helper\FormUrl::class,
            'formWeek'                => \Laminas\Form\View\Helper\FormWeek::class,
        ],
        'initializers' => [
            \Common\Form\View\Helper\TranslatableAttributePrefixInitializer::class,
        ],
        'factories' => [
            'applicationName' => \Common\View\Helper\ApplicationNameFactory::class,
            'version' => \Common\View\Helper\VersionFactory::class,
            'pageId' => \Common\View\Helper\PageIdFactory::class,
            'pageTitle' => \Common\View\Helper\PageTitleFactory::class,
            'licenceChecklist' => \Common\View\Helper\LicenceChecklistFactory::class,
            \Common\View\Helper\Date::class => \Common\View\Helper\DateFactory::class,
            \Common\Form\View\Helper\FormRow::class => \Common\Form\View\Helper\FormRowFactory::class,
            \Common\View\Helper\LanguageLink::class => \Common\View\Helper\LanguageLinkFactory::class,
            'currentUser' => \Common\View\Helper\CurrentUserFactory::class,
            'systemInfoMessages' => \Common\View\Factory\Helper\SystemInfoMessagesFactory::class,
            'linkBack' => Common\View\Helper\LinkBackFactory::class,
            'translateReplace' => \Common\View\Helper\TranslateReplaceFactory::class,
            'flashMessengerAll' => \Common\View\Factory\Helper\FlashMessengerFactory::class,
            \Common\View\Helper\EscapeHtml::class => \Common\View\Factory\Helper\EscapeHtmlFactory::class,
            \Common\Form\View\Helper\FormElementErrors::class => \Common\Form\View\Helper\FormElementErrorsFactory::class,
            \Common\Form\View\Helper\FormErrors::class => \Common\Form\View\Helper\FormErrorsFactory::class,
            \Common\Form\View\Helper\FormElement::class => \Common\Form\View\Helper\FormElementFactory::class,
            \Common\View\Helper\Config::class => \Common\View\Helper\ConfigFactory::class,
            'IsGranted' => \LmcRbacMvc\Factory\IsGrantedPluginFactory::class
        ],
        'aliases' => [
            'formElement' => \Common\Form\View\Helper\FormElement::class,
            'FormElement' => \Common\Form\View\Helper\FormElement::class,
            'formrow' => \Common\Form\View\Helper\FormRow::class,
            'formcollection' => \Common\Form\View\Helper\FormCollection::class,
            'formCollection' => Common\Form\View\Helper\FormCollection::class,
            'FormCollection' => Common\Form\View\Helper\FormCollection::class,
            'form_collection' => \Common\Form\View\Helper\FormCollection::class,
            'formRow' => \Common\Form\View\Helper\FormRow::class,
            'formelement' => \Common\Form\View\Helper\FormElement::class,
            'form_element' => \Common\Form\View\Helper\FormElement::class,
            'formElementErrors' => \Common\Form\View\Helper\FormElementErrors::class,
            'formelementerrors' => \Common\Form\View\Helper\FormElementErrors::class,
            'form_element_errors' => \Common\Form\View\Helper\FormElementErrors::class,
            'formErrors' => \Common\Form\View\Helper\FormErrors::class,
            'formerrors' => \Common\Form\View\Helper\FormErrors::class,
            'config' => \Common\View\Helper\Config::class,
            'escapeHtml' => \Common\View\Helper\EscapeHtml::class,
            'formradio' => FormRadio::class,
            'formradiooption' => \Common\Form\View\Helper\FormRadioOption::class,
            'formradiohorizontal' => \Common\Form\View\Helper\FormRadioHorizontal::class,
            'formcheckboxadvanced' => \Common\Form\View\Helper\FormCheckboxAdvanced::class,
            'formradiovertical' => \Common\Form\View\Helper\FormRadioVertical::class,
            'formdatetime' => \Common\Form\View\Helper\FormDateTimeSelect::class,
            'formdateselect' => \Common\Form\View\Helper\FormDateSelect::class,
            'forminputsearch' => FormInputSearch::class,
            'formplaintext' => \Common\Form\View\Helper\FormPlainText::class,
            'form_plain_text' => \Common\Form\View\Helper\FormPlainText::class,
            'addtags' => \Common\View\Helper\AddTags::class,
            'transportmanagerapplicationstatus' => \Common\View\Helper\TransportManagerApplicationStatus::class,
            'status' => \Common\View\Helper\Status::class,
            'address' => \Common\View\Helper\Address::class,
            'personname' => \Common\View\Helper\PersonName::class,
            'Address' => \Common\View\Helper\Address::class,
            'datetime' => \Common\View\Helper\DateTime::class,
            'returntoaddress' => Common\View\Helper\ReturnToAddress::class,
            'navigationparentpage' => Common\View\Helper\NavigationParentPage::class,
            'panel' => Panel::class,
            'link' => Common\View\Helper\Link::class,
            'linknewwindow' => Common\View\Helper\LinkNewWindow::class,
            'linknewwindowexternal' => Common\View\Helper\LinkNewWindowExternal::class,
            'linkmodal' => Common\View\Helper\LinkModal::class,
            'readonlyformfieldset' => ReadonlyFormHelper\FormFieldset::class,
            'readonlyformfileuploadlist' => ReadonlyFormHelper\FormFileUploadList::class,
            'readonlyformitem' => ReadonlyFormHelper\FormItem::class,
            'readonlyformselect' => ReadonlyFormHelper\FormSelect::class,
            'readonlyformdateselect' => ReadonlyFormHelper\FormDateSelect::class,
            'readonlyformrow' => ReadonlyFormHelper\FormRow::class,
            'readonlyformtable' => ReadonlyFormHelper\FormTable::class,
            'readonlyactions' => \Common\View\Helper\ReadOnlyActions::class,
            'currencyformatter' => \Common\View\Helper\CurrencyFormatter::class,
            'applicationname' => \Common\View\Helper\ApplicationNameFactory::class,
            'pageid' => \Common\View\Helper\PageIdFactory::class,
            'pagetitle' => \Common\View\Helper\PageTitleFactory::class,
            'licencechecklist' => \Common\View\Helper\LicenceChecklistFactory::class,
            'date' => \Common\View\Helper\Date::class,
            'languageLink' => \Common\View\Helper\LanguageLink::class,
            'languagelink' => \Common\View\Helper\LanguageLink::class,
            'currentuser' => 'currentUser',
            'systeminfomessages' => 'systemInfoMessages',
            'linkback' => 'linkBack',
            'translatereplace' => 'translateReplace',
            'flashmessengerall' => 'flashMessengerAll',
            'escapehtml' => \Common\View\Helper\EscapeHtml::class,
            'isgranted' => \LmcRbacMvc\Factory\IsGrantedPluginFactory::class,
            'formbutton'              => \Laminas\Form\View\Helper\FormButton::class,
            'formcaptcha'             => \Laminas\Form\View\Helper\FormCaptcha::class,
            'formcheckbox'            => \Laminas\Form\View\Helper\FormCheckbox::class,
            'formcolor'               => \Laminas\Form\View\Helper\FormColor::class,
            'formdate'                => \Laminas\Form\View\Helper\FormDate::class,
            'formdatetimelocal'       => \Laminas\Form\View\Helper\FormDateTimeLocal::class,
            'formemail'               => \Laminas\Form\View\Helper\FormEmail::class,
            'formfile'                => \Laminas\Form\View\Helper\FormFile::class,
            'formhidden'              => \Laminas\Form\View\Helper\FormHidden::class,
            'formimage'               => \Laminas\Form\View\Helper\FormImage::class,
            'forminput'               => \Laminas\Form\View\Helper\FormInput::class,
            'formlabel'               => \Laminas\Form\View\Helper\FormLabel::class,
            'formmonth'               => \Laminas\Form\View\Helper\FormMonth::class,
            'formmonthselect'         => \Laminas\Form\View\Helper\FormMonthSelect::class,
            'formmulticheckbox'       => \Laminas\Form\View\Helper\FormMultiCheckbox::class,
            'formnumber'              => \Laminas\Form\View\Helper\FormNumber::class,
            'formpassword'            => \Laminas\Form\View\Helper\FormPassword::class,
            'formrange'               => \Laminas\Form\View\Helper\FormRange::class,
            'formreset'               => \Laminas\Form\View\Helper\FormReset::class,
            'formsearch'              => \Laminas\Form\View\Helper\FormSearch::class,
            'formselect'              => \Laminas\Form\View\Helper\FormSelect::class,
            'formsubmit'              => \Laminas\Form\View\Helper\FormSubmit::class,
            'formtel'                 => \Laminas\Form\View\Helper\FormTel::class,
            'formtext'                => \Laminas\Form\View\Helper\FormText::class,
            'formtime'                => \Laminas\Form\View\Helper\FormTime::class,
            'formurl'                 => \Laminas\Form\View\Helper\FormUrl::class,
            'formweek'                => \Laminas\Form\View\Helper\FormWeek::class,
            'formtextarea'            => \Laminas\Form\View\Helper\FormTextarea::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'partials/view' => __DIR__ . '/../view',
            'translations' => __DIR__ . '/../config/language/partials'
        ]
    ],
    'local_scripts_path' => [__DIR__ . '/../src/Common/assets/js/inline/'],
    'forms_path' => __DIR__ . '/../../Common/src/Common/Form/Forms/',
    'form_elements' => [
        'invokables' => [
            'DateSelect' => \Common\Form\Elements\Custom\DateSelect::class,
            'MonthSelect' => \Common\Form\Elements\Custom\MonthSelect::class,
            'YearSelect' => \Common\Form\Elements\Custom\YearSelect::class,
            'DateTimeSelect' => \Common\Form\Elements\Custom\DateTimeSelect::class,
            \Common\Form\Elements\Custom\OlcsCheckbox::class => \Common\Form\Elements\Custom\OlcsCheckbox::class,
            TextArea::class => TextArea::class,

        ],
        'factories' => [
            DynamicSelect::class => DynamicSelectFactory::class,
            DynamicMultiCheckbox::class => DynamicMultiCheckboxFactory::class,
            DynamicRadio::class => DynamicRadioFactory::class,
            DynamicRadioHtml::class => DynamicRadioHtmlFactory::class,
            Common\Form\Elements\Types\Table::class => Common\Form\Elements\Types\Table::class
        ],
        'aliases' => [
            'DynamicSelect' => DynamicSelect::class,
            'DynamicMultiCheckbox' => DynamicMultiCheckbox::class,
            'DynamicRadio' => DynamicRadio::class,
            'DynamicRadioHtml' => DynamicRadioHtml::class,
            'OlcsCheckbox' => OlcsCheckbox::class,
            'TextArea' => TextArea::class,
            'Table' => \Common\Form\Elements\Types\Table::class
        ]
    ],
    'validation' => [

        /**
         * Configures which message templates should have their default message templates replaced.
         *
         * Entries should map a validation message key to the validator class that yields the validation messages for
         * that key
         *
         * "validation message key" => "validator class reference"
         *
         * @type array
         */
        'default_message_templates_to_replace' => [
            \Laminas\Validator\NotEmpty::IS_EMPTY => \Laminas\Validator\NotEmpty::class,
        ],
    ],
    'validators' => [
        'invokables' => [
            \Common\Validator\MustRemainOperatorAdmin::class => \Common\Validator\MustRemainOperatorAdmin::class,
            \Common\Validator\ValidateIfMultiple::class => \Common\Validator\ValidateIfMultiple::class,
            \Common\Validator\DateCompare::class => \Common\Validator\DateCompare::class,
            \Common\Validator\NumberCompare::class => \Common\Validator\NumberCompare::class,
            \Common\Validator\SumCompare::class => \Common\Validator\SumCompare::class,
            \Common\Form\Elements\Validators\DateNotInFuture::class => \Common\Form\Elements\Validators\DateNotInFuture::class,
            \Common\Validator\OneOf::class => \Common\Validator\OneOf::class,
            \Common\Form\Elements\Validators\Date::class => \Common\Form\Elements\Validators\Date::class,
            \Common\Validator\DateInFuture::class => \Common\Validator\DateInFuture::class,
            \Common\Validator\DateCompareWithInterval::class => \Common\Validator\DateCompareWithInterval::class,
            \Common\Validator\FileUploadCount::class => \Common\Validator\FileUploadCount::class,
            TableRequiredValidator::class => TableRequiredValidator::class
        ],
        'aliases' => [
            'ValidateIf' => Common\Validator\ValidateIf::class,
            'validateIf' => Common\Validator\ValidateIf::class,
            'ValidateIfMultiple' => \Common\Validator\ValidateIfMultiple::class,
            'DateCompare' => \Common\Validator\DateCompare::class,
            'NumberCompare' => \Common\Validator\NumberCompare::class,
            'SumCompare' => \Common\Validator\SumCompare::class,
            'DateNotInFuture' => \Common\Form\Elements\Validators\DateNotInFuture::class,
            'OneOf' => \Common\Validator\OneOf::class,
            'Date' => \Common\Form\Elements\Validators\Date::class,
            'DateInFuture' => \Common\Validator\DateInFuture::class,
            'DateCompareWithInterval' => \Common\Validator\DateCompareWithInterval::class,
        ],
        'factories' => [
            QaService\DateNotInPastValidator::class => QaService\DateNotInPastValidatorFactory::class,
            QaService\Custom\Common\DateBeforeValidator::class => QaService\Custom\Common\DateBeforeValidatorFactory::class,
            Common\Validator\ValidateIf::class => InvokableFactory::class,
        ]
    ],
    'filters' => [
        'invokables' => [
            \Common\Filter\DateSelectNullifier::class => \Common\Filter\DateSelectNullifier::class,
            \Common\Filter\DateTimeSelectNullifier::class => \Common\Filter\DateTimeSelectNullifier::class,
            \Common\Filter\DecompressUploadToTmp::class => \Common\Filter\DecompressUploadToTmp::class,
            \Common\Filter\DecompressToTmp::class => \Common\Filter\DecompressToTmp::class
        ],
        'delegators' => [
            \Common\Filter\DecompressUploadToTmp::class => [\Common\Filter\DecompressToTmpDelegatorFactory::class],
            \Common\Filter\DecompressToTmp::class => [\Common\Filter\DecompressToTmpDelegatorFactory::class]
        ],
        'aliases' => [
            'DateSelectNullifier' => \Common\Filter\DateSelectNullifier::class,
            'DateTimeSelectNullifier' => \Common\Filter\DateTimeSelectNullifier::class,
            'DecompressUploadToTmp' => \Common\Filter\DecompressUploadToTmp::class,
            'DecompressToTmp' => \Common\Filter\DecompressToTmp::class
        ]
    ],
    'data_services' => [
        'factories' => [
            DataService\ApplicationOperatingCentre::class => DataService\ApplicationOperatingCentreFactory::class,
            DataService\LicenceOperatingCentre::class => DataService\LicenceOperatingCentreFactory::class,
            DataService\OcContextListDataService::class => DataService\OcContextListDataServiceFactory::class,
            DataService\Venue::class => DataService\VenueFactory::class,
            DataService\Search\Search::class => DataService\Search\SearchFactory::class,
            SearchType::class => SearchType::class,
            DataService\AbstractDataServiceServices::class => DataService\AbstractDataServiceServicesFactory::class,
            DataService\AbstractListDataServiceServices::class => DataService\AbstractListDataServiceServicesFactory::class,
            DataService\AddressDataService::class => DataService\AbstractDataServiceFactory::class,
            DataService\Application::class => DataService\AbstractDataServiceFactory::class,
            DataService\ApplicationPathGroup::class => DataService\AbstractDataServiceFactory::class,
            DataService\BusRegBrowseListDataService::class => DataService\AbstractDataServiceFactory::class,
            DataService\BusRegSearchViewListDataService::class => DataService\AbstractDataServiceFactory::class,
            'category' => DataService\CategoryDataService::class,
            DataService\ContactDetails::class => DataService\AbstractListDataServiceFactory::class,
            DataService\Country::class => DataService\AbstractDataServiceFactory::class,
            'country' => DataService\AbstractDataServiceFactory::class,
            DataService\FeeType::class => DataService\AbstractDataServiceFactory::class,
            DataService\FeeTypeDataService::class => DataService\AbstractDataServiceFactory::class,
            DataService\IrhpPermitType::class => DataService\AbstractDataServiceFactory::class,
            DataService\Licence::class => DataService\AbstractDataServiceFactory::class,
            DataService\LocalAuthority::class => DataService\AbstractDataServiceFactory::class,
            DataService\RefData::class => DataService\RefDataFactory::class,
            DataService\RefDataServices::class => DataService\RefDataServicesFactory::class,
            DataService\Role::class => DataService\AbstractDataServiceFactory::class,
            DataService\SiCategoryType::class => DataService\AbstractDataServiceFactory::class,
            'staticList' => DataService\StaticListFactory::class,
            DataService\Surrender::class => DataService\AbstractDataServiceFactory::class,
            DataService\TrafficArea::class => DataService\AbstractDataServiceFactory::class,
            DataService\MessagingSubject::class => DataService\AbstractListDataServiceFactory::class,
        ]
    ],
    'tables' => [
        'config' => [
            __DIR__ . '/../src/Common/Table/Tables/'
        ],
        'partials' => [
            'html' => __DIR__ . '/../view/table/',
            'csv' => __DIR__ . '/../view/table/csv'
        ]
    ],
    'fieldsets_path' => __DIR__ . '/../../Common/src/Common/Form/Fieldsets/',
    'static-list-data' => include __DIR__ . '/list-data/static-list-data.php',
    'form' => [
        'element' => [
            'renderers' => [
                \Common\Form\Elements\Custom\RadioVertical::class => \Common\Form\View\Helper\FormRadioVertical::class
            ],
        ],
    ],
    'rest_services' => [
        'abstract_factories' => [
            \Common\Service\Api\AbstractFactory::class
        ]
    ],
    'service_api_mapping' => [
        'endpoints' => [
            'payments' => 'http://olcspayment.dev/api/',
            'backend' => 'http://olcs-backend/',
            'postcode' => 'http://postcode.cit.olcs.mgt.mtpdvsa/',
        ]
    ],
    'lmc_rbac' => [
        'identity_provider' => IdentityProviderInterface::class,
        'role_provider' => [\Common\Rbac\Role\RoleProvider::class => []],
        'role_provider_manager' => [
            'factories' => [
                \Common\Rbac\Role\RoleProvider::class => \Common\Rbac\Role\RoleProviderFactory::class
            ]
        ],
        'protection_policy' => \LmcRbacMvc\Guard\GuardInterface::POLICY_DENY,
    ],
    'form_service_manager' => [
        'abstract_factories' => [
            FormServiceAbstractFactory::class
        ],
        'aliases' => FormServiceAbstractFactory::FORM_SERVICE_CLASS_ALIASES,
        'factories' => [
            Declaration::class => DeclarationFactory::class,
            ConditionsUndertakings::class => ConditionsUndertakingsFactory::class
        ],
    ],
    'translator_plugins' => [
        'factories' => [
            TranslationLoader::class => TranslationLoaderFactory::class
        ],
    ],
    'translator' => [
        'locale' => [
            'en_GB', //default locale
            'en_GB', //fallback locale
        ],
        'remote_translation' => [
            [
                'type' => TranslationLoader::class,
            ]
        ],
    ],
];
