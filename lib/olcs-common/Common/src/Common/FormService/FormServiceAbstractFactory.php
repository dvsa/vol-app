<?php

namespace Common\FormService;

use Common\FormService\Form\Continuation\ConditionsUndertakings;
use Common\FormService\Form\Continuation\LicenceChecklist;
use Common\FormService\Form\Continuation\Payment;
use Common\FormService\Form\Continuation\Start;
use Common\FormService\Form\Lva\Addresses;
use Common\FormService\Form\Lva\Application;
use Common\FormService\Form\Lva\ApplicationGoodsVehicles;
use Common\FormService\Form\Lva\ApplicationPsvVehiclesVehicle;
use Common\FormService\Form\Lva\BusinessDetails\ApplicationBusinessDetails;
use Common\FormService\Form\Lva\BusinessDetails\LicenceBusinessDetails;
use Common\FormService\Form\Lva\BusinessDetails\VariationBusinessDetails;
use Common\FormService\Form\Lva\BusinessType\ApplicationBusinessType;
use Common\FormService\Form\Lva\BusinessType\LicenceBusinessType;
use Common\FormService\Form\Lva\BusinessType\VariationBusinessType;
use Common\FormService\Form\Lva\CommonGoodsVehiclesFilters;
use Common\FormService\Form\Lva\CommonPsvVehiclesFilters;
use Common\FormService\Form\Lva\CommonVehiclesSearch;
use Common\FormService\Form\Lva\CommunityLicences\ApplicationCommunityLicences;
use Common\FormService\Form\Lva\CommunityLicences\LicenceCommunityLicences;
use Common\FormService\Form\Lva\CommunityLicences\VariationCommunityLicences;
use Common\FormService\Form\Lva\ConditionsUndertakings\ApplicationConditionsUndertakings;
use Common\FormService\Form\Lva\ConditionsUndertakings\LicenceConditionsUndertakings;
use Common\FormService\Form\Lva\ConditionsUndertakings\VariationConditionsUndertakings;
use Common\FormService\Form\Lva\ConvictionsPenalties;
use Common\FormService\Form\Lva\FinancialEvidence;
use Common\FormService\Form\Lva\FinancialHistory;
use Common\FormService\Form\Lva\GenericVehiclesVehicle;
use Common\FormService\Form\Lva\Licence;
use Common\FormService\Form\Lva\LicenceGoodsVehicles;
use Common\FormService\Form\Lva\LicenceGoodsVehiclesFilters;
use Common\FormService\Form\Lva\LicenceHistory;
use Common\FormService\Form\Lva\LicencePsvVehiclesVehicle;
use Common\FormService\Form\Lva\LicenceTaxiPhv;
use Common\FormService\Form\Lva\LicenceVariationVehicles;
use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre;
use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres;
use Common\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres;
use Common\FormService\Form\Lva\People\ApplicationPeople;
use Common\FormService\Form\Lva\People\LicenceAddPerson;
use Common\FormService\Form\Lva\People\LicencePeople;
use Common\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader;
use Common\FormService\Form\Lva\People\SoleTrader\LicenceSoleTrader;
use Common\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader;
use Common\FormService\Form\Lva\People\VariationPeople;
use Common\FormService\Form\Lva\PsvDiscs;
use Common\FormService\Form\Lva\PsvVehicles;
use Common\FormService\Form\Lva\Safety;
use Common\FormService\Form\Lva\TaxiPhv;
use Common\FormService\Form\Lva\TransportManager\ApplicationTransportManager;
use Common\FormService\Form\Lva\TransportManager\LicenceTransportManager;
use Common\FormService\Form\Lva\TransportManager\VariationTransportManager;
use Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence;
use Common\FormService\Form\Lva\TypeOfLicence\LicenceTypeOfLicence;
use Common\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence;
use Common\FormService\Form\Lva\Undertakings;
use Common\FormService\Form\Lva\Variation;
use Common\FormService\Form\Lva\VariationFinancialEvidence;
use Common\FormService\Form\Lva\VariationGoodsVehicles;
use Common\FormService\Form\Lva\VariationPsvVehicles;
use Common\FormService\Form\Lva\VehiclesDeclarationsMainUndertakings;
use Common\FormService\Form\Lva\VehiclesDeclarationsNovelty;
use Common\FormService\Form\Lva\VehiclesDeclarationsPsvOperateLarge;
use Common\FormService\Form\Lva\VehiclesDeclarationsPsvOperateSmall;
use Common\FormService\Form\Lva\VehiclesDeclarationsSize;
use Common\FormService\Form\Lva\VehiclesDeclarationsEvidenceLarge;
use Common\FormService\Form\Lva\VehiclesDeclarationsEvidenceSmall;
use Common\FormService\Form\Lva\VehiclesDeclarationsSmallConditions;
use Common\FormService\Form\Lva\VehiclesDeclarationsWritten;
use Common\Rbac\Service\Permission;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

class FormServiceAbstractFactory implements AbstractFactoryInterface
{
    public const FORM_SERVICE_CLASS_ALIASES = [
        // OC Forms
        'lva-licence-operating_centres' => LicenceOperatingCentres::class,
        'lva-variation-operating_centres' => VariationOperatingCentres::class,
        'lva-licence-operating_centre' => CommonOperatingCentre::class,
        'lva-variation-operating_centre' => CommonOperatingCentre::class,
        'lva-application-operating_centre' => CommonOperatingCentre::class,

        // Business type forms
        'lva-application-business_type' => ApplicationBusinessType::class,
        'lva-licence-business_type' => LicenceBusinessType::class,
        'lva-variation-business_type' => VariationBusinessType::class,

        // Lva form services
        'lva-licence' => Licence::class,
        'lva-variation' => Variation::class,
        'lva-application' => Application::class,

        // Business details form services
        'lva-licence-business_details' => LicenceBusinessDetails::class,
        'lva-variation-business_details' => VariationBusinessDetails::class,
        'lva-application-business_details' => ApplicationBusinessDetails::class,

        // Addresses form services
        'lva-licence-addresses' => Addresses::class,
        'lva-variation-addresses' => Addresses::class,
        'lva-application-addresses' => Addresses::class,

        // Goods vehicle form services
        'lva-licence-goods-vehicles' => LicenceGoodsVehicles::class,
        'lva-variation-goods-vehicles' => VariationGoodsVehicles::class,
        'lva-application-goods-vehicles' => ApplicationGoodsVehicles::class,

        // Psv vehicles vehicle form services
        'lva-licence-vehicles_psv-vehicle' => LicencePsvVehiclesVehicle::class,
        'lva-variation-vehicles_psv-vehicle' => ApplicationPsvVehiclesVehicle::class,
        'lva-application-vehicles_psv-vehicle' => ApplicationPsvVehiclesVehicle::class,

        // Goods vehicle filter form services
        'lva-licence-goods-vehicles-filters' => LicenceGoodsVehiclesFilters::class,
        'lva-variation-goods-vehicles-filters' => CommonGoodsVehiclesFilters::class,
        'lva-application-goods-vehicles-filters' => CommonGoodsVehiclesFilters::class,

        // PSV filter form services
        'lva-psv-vehicles-filters' => CommonPsvVehiclesFilters::class,

        // Vehicle search form services
        'lva-vehicles-search' => CommonVehiclesSearch::class,

        // Common vehicle services
        'lva-licence-variation-vehicles' => LicenceVariationVehicles::class,
        'lva-generic-vehicles-vehicle' => GenericVehiclesVehicle::class,

        // Type of licence
        'lva-licence-type-of-licence' => LicenceTypeOfLicence::class,
        'lva-application-type-of-licence' => ApplicationTypeOfLicence::class,
        'lva-variation-type-of-licence' => VariationTypeOfLicence::class,

        // People form services
        'lva-licence-people' => LicencePeople::class,
        'lva-licence-addperson' => LicenceAddPerson::class,
        'lva-variation-people' => VariationPeople::class,
        'lva-application-people' => ApplicationPeople::class,
        'lva-licence-sole_trader' => LicenceSoleTrader::class,
        'lva-variation-sole_trader' => VariationSoleTrader::class,
        'lva-application-sole_trader' => ApplicationSoleTrader::class,

        // Community Licences form services
        'lva-licence-community_licences' => LicenceCommunityLicences::class,
        'lva-variation-community_licences' => VariationCommunityLicences::class,
        'lva-application-community_licences'
        => ApplicationCommunityLicences::class,

        // Safety form services
        'lva-licence-safety' => Safety::class,
        'lva-variation-safety' => Safety::class,
        'lva-application-safety' => Safety::class,

        // Conditions and Undertakings form services
        'lva-licence-conditions_undertakings'
        => LicenceConditionsUndertakings::class,
        'lva-variation-conditions_undertakings'
        => VariationConditionsUndertakings::class,
        'lva-application-conditions_undertakings'
        => ApplicationConditionsUndertakings::class,

        // Financial History form services
        'lva-licence-financial_history' => FinancialHistory::class,
        'lva-variation-financial_history' => FinancialHistory::class,
        'lva-application-financial_history' => FinancialHistory::class,

        // Financial Evidence form services
        'lva-variation-financial_evidence' => VariationFinancialEvidence::class,
        'lva-application-financial_evidence' => FinancialEvidence::class,

        // Declarations (undertakings) form services
        'lva-variation-undertakings' => Undertakings::class,
        'lva-application-undertakings' => Undertakings::class,

        // Taxi/PHV form services
        'lva-licence-taxi_phv' => LicenceTaxiPhv::class,
        'lva-variation-taxi_phv' => TaxiPhv::class,
        'lva-application-taxi_phv' => TaxiPhv::class,

        // Licence History form services
        'lva-application-licence_history' => LicenceHistory::class,
        'lva-variation-licence_history' => LicenceHistory::class,

        // Convictions & Penalties form services
        'lva-variation-convictions_penalties' => ConvictionsPenalties::class,
        'lva-application-convictions_penalties' => ConvictionsPenalties::class,

        // New vehicle declarations form services (PSV restricted)
        'lva-variation-vehicles_declarations_vehicles_size' => VehiclesDeclarationsSize::class,
        'lva-application-vehicles_declarations_vehicles_size' => VehiclesDeclarationsSize::class,
        'lva-variation-vehicles_declarations_psv_operate_large' => VehiclesDeclarationsPsvOperateLarge::class,
        'lva-application-vehicles_declarations_psv_operate_large' => VehiclesDeclarationsPsvOperateLarge::class,
        'lva-variation-vehicles_declarations_psv_operate_small' => VehiclesDeclarationsPsvOperateSmall::class,
        'lva-application-vehicles_declarations_psv_operate_small' => VehiclesDeclarationsPsvOperateSmall::class,
        'lva-variation-vehicles_declarations_psv_small_conditions' => VehiclesDeclarationsSmallConditions::class,
        'lva-application-vehicles_declarations_psv_small_conditions' => VehiclesDeclarationsSmallConditions::class,
        'lva-variation-vehicles_declarations_psv_operate_novelty' => VehiclesDeclarationsNovelty::class,
        'lva-application-vehicles_declarations_psv_operate_novelty' => VehiclesDeclarationsNovelty::class,
        'lva-variation-vehicles_declarations_psv_small_part_written' => VehiclesDeclarationsWritten::class,
        'lva-application-vehicles_declarations_psv_small_part_written' => VehiclesDeclarationsWritten::class,
        'lva-variation-vehicles_declarations_psv_documentary_evidence_small' => VehiclesDeclarationsEvidenceSmall::class,
        'lva-application-vehicles_declarations_psv_documentary_evidence_small' => VehiclesDeclarationsEvidenceSmall::class,
        'lva-variation-vehicles_declarations_psv_documentary_evidence_large' => VehiclesDeclarationsEvidenceLarge::class,
        'lva-application-vehicles_declarations_psv_documentary_evidence_large' => VehiclesDeclarationsEvidenceLarge::class,
        'lva-variation-vehicles_declarations_psv_main_occupation_undertakings' => VehiclesDeclarationsMainUndertakings::class,
        'lva-application-vehicles_declarations_psv_main_occupation_undertakings' => VehiclesDeclarationsMainUndertakings::class,

        // PSV Vehicles form services
        'lva-licence-vehicles_psv' => PsvVehicles::class,
        'lva-variation-vehicles_psv' => VariationPsvVehicles::class,
        'lva-application-vehicles_psv' => PsvVehicles::class,

        // Discs form services
        'lva-licence-discs' => PsvDiscs::class,
        'lva-variation-discs' => PsvDiscs::class,

        'lva-licence-transport_managers' => LicenceTransportManager::class,
        'lva-variation-transport_managers' => VariationTransportManager::class,
        'lva-application-transport_managers' => ApplicationTransportManager::class,

        // Continuation forms
        'continuations-checklist' => LicenceChecklist::class,
        'continuations-start' => Start::class,
        'continuations-payment' => Payment::class,
        'Lva\Application' => Application::class,
        'Lva\Licence' => Licence::class,
        'Lva\Variation' => Variation::class,
    ];


    #[\Override]
    public function canCreate($container, $requestedName): bool
    {
        return in_array($requestedName, self::FORM_SERVICE_CLASS_ALIASES);
    }

    #[\Override]
    public function __invoke($container, $requestedName, array $options = null)
    {
        /** @var FormServiceManager $formServiceLocator */
        /** @var FormHelperService $formHelper */
        /** @var AuthorizationService $authService */
        /** @var GuidanceHelperService $guidanceHelper */
        /** @var UrlHelperService $urlHelper */
        /** @var TranslationHelperService $translator */
        /** @var PeopleLvaService $peopleLvaService */
        /** @var ScriptFactory $scriptFactory */
        /** @var $tableBuilder */

        $serviceLocator = $container;
        $formHelper = $serviceLocator->get(FormHelperService::class);

        switch ($requestedName) {
            // OC Forms
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-operating_centre']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-operating_centre']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-operating_centre']:
                return new CommonOperatingCentre($formHelper);

            // Addresses
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-addresses']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-addresses']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-addresses']:
                return new Addresses($formHelper);

            // Goods vehicle filter form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles-filters']:
                return new LicenceGoodsVehiclesFilters($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles-filters']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-goods-vehicles-filters']:
                return new CommonGoodsVehiclesFilters($formHelper);

            // PSV filter form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-psv-vehicles-filters']:
                return new CommonPsvVehiclesFilters($formHelper);

            // Vehicle search form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-vehicles-search']:
                return new CommonVehiclesSearch($formHelper);

            // Common vehicle services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-variation-vehicles']:
                return new LicenceVariationVehicles($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-generic-vehicles-vehicle']:
                return new GenericVehiclesVehicle($formHelper);

            // Safety form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-safety']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-safety']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-safety']:
                return new Safety($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-operating_centres']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $tableBuilder = $serviceLocator->get(TableFactory::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceOperatingCentres($formHelper, $authService, $tableBuilder, $formServiceLocator);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-operating_centres']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $tableBuilder = $serviceLocator->get(TableFactory::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                return new VariationOperatingCentres($formHelper, $authService, $tableBuilder, $formServiceLocator, $translator);

            // Business type forms
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-business_type']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $guidanceHelper = $serviceLocator->get(GuidanceHelperService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationBusinessType($formHelper, $authService, $guidanceHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-business_type']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $guidanceHelper = $serviceLocator->get(GuidanceHelperService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceBusinessType($formHelper, $authService, $guidanceHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-business_type']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $guidanceHelper = $serviceLocator->get(GuidanceHelperService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationBusinessType($formHelper, $authService, $guidanceHelper, $formServiceLocator);

            // Lva form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new Licence($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new Variation($formHelper, $authService);
            case Application::class:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new Application($formHelper, $authService);

            // Business details form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceBusinessDetails($formHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationBusinessDetails($formHelper, $formServiceLocator);
            case ApplicationBusinessDetails::class:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationBusinessDetails($formHelper, $formServiceLocator);

            // Goods vehicle form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceGoodsVehicles($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-goods-vehicles']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationGoodsVehicles($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationGoodsVehicles($formHelper, $authService, $formServiceLocator);

            // Psv vehicles vehicle form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-vehicles_psv-vehicle']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicencePsvVehiclesVehicle($formHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_psv-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-vehicles_psv-vehicle']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationPsvVehiclesVehicle($formHelper, $formServiceLocator);

            // Type of licence
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-type-of-licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceTypeOfLicence($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-type-of-licence']:
                $permissionService = $serviceLocator->get(Permission::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationTypeOfLicence($formHelper, $permissionService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-type-of-licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationTypeOfLicence($formHelper, $authService, $formServiceLocator);

            // People form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-people']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicencePeople($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-addperson']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicenceAddPerson($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-people']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new VariationPeople($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-people']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationPeople($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-sole_trader']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $peopleLvaService = $serviceLocator->get(PeopleLvaService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceSoleTrader($formHelper, $authService, $peopleLvaService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-sole_trader']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $peopleLvaService = $serviceLocator->get(PeopleLvaService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationSoleTrader($formHelper, $authService, $peopleLvaService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-sole_trader']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $peopleLvaService = $serviceLocator->get(PeopleLvaService::class);
                return new ApplicationSoleTrader($formHelper, $authService, $peopleLvaService);

            // Community Licences form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-community_licences']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicenceCommunityLicences($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-community_licences']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new VariationCommunityLicences($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-community_licences']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationCommunityLicences($formHelper, $authService);

            // Conditions and Undertakings form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-conditions_undertakings']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicenceConditionsUndertakings($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-conditions_undertakings']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new VariationConditionsUndertakings($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-conditions_undertakings']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationConditionsUndertakings($formHelper, $authService);

            // Financial History form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-financial_history']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-financial_history']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-financial_history']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                return new FinancialHistory($formHelper, $translator);

            // Financial Evidence form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-financial_evidence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new VariationFinancialEvidence($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-financial_evidence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new FinancialEvidence($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_declarations_psv_documentary_evidence_large']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-vehicles_declarations_psv_documentary_evidence_large']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new VehiclesDeclarationsEvidenceLarge($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_declarations_psv_documentary_evidence_small']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-vehicles_declarations_psv_documentary_evidence_small']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new VehiclesDeclarationsEvidenceSmall($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);

            // Declarations (undertakings) form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-undertakings']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-undertakings']:
                return new Undertakings($formHelper);

            // Taxi/PHV form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-taxi_phv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicenceTaxiPhv($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-taxi_phv']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-taxi_phv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new TaxiPhv($formHelper, $authService);

            // Licence History form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-licence_history']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-licence_history']:
                return new LicenceHistory($formHelper);

            // Convictions & Penalties form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-convictions_penalties']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-convictions_penalties']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                return new ConvictionsPenalties($formHelper, $translator, $urlHelper);

            // PSV Vehicles form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_psv']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-vehicles_psv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new PsvVehicles($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-vehicles_psv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new VariationPsvVehicles($formHelper, $authService);

            // Discs form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-discs']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-discs']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new PsvDiscs($formHelper, $authService);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-transport_managers']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicenceTransportManager($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-transport_managers']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new VariationTransportManager($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-transport_managers']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationTransportManager($formHelper, $authService);

            // Continuation forms
            case self::FORM_SERVICE_CLASS_ALIASES['continuations-checklist']:
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                return new LicenceChecklist($formHelper, $urlHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['continuations-start']:
                return new Start($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['continuations-payment']:
                $guidanceHelper = $serviceLocator->get(GuidanceHelperService::class);
                return new Payment($formHelper, $guidanceHelper);
            case ConditionsUndertakings::class:
                return new ConditionsUndertakings($formHelper);
        }

        // Factory should have been able to satisfy this request but nothing was returned yet, so throw an exception
        throw new InvalidServiceException(sprintf(
            'FormServiceAbstractFactory claimed to be able to supply instance of type "%s", but nothing was returned',
            $requestedName
        ));
    }
}
