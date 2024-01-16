<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\ConvictionsPenalties;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Lva\PeopleLvaService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Olcs\FormService\Form\Lva\Addresses\ApplicationAddresses;
use Olcs\FormService\Form\Lva\Addresses\LicenceAddresses;
use Olcs\FormService\Form\Lva\Addresses\VariationAddresses;
use Olcs\FormService\Form\Lva\BusinessType\ApplicationBusinessType;
use Olcs\FormService\Form\Lva\BusinessType\LicenceBusinessType;
use Olcs\FormService\Form\Lva\BusinessType\VariationBusinessType;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;
use Olcs\FormService\Form\Lva\OperatingCentres\ApplicationOperatingCentres;
use Olcs\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres;
use Olcs\FormService\Form\Lva\OperatingCentres\VariationOperatingCentres;
use Olcs\FormService\Form\Lva\People\ApplicationPeople;
use Olcs\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader;
use Olcs\FormService\Form\Lva\People\SoleTrader\LicenceSoleTrader;
use Olcs\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader;
use Olcs\FormService\Form\Lva\TransportManager\ApplicationTransportManager;
use Olcs\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence;
use Olcs\FormService\Form\Lva\TypeOfLicence\LicenceTypeOfLicence;
use Olcs\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence;
use LmcRbacMvc\Service\AuthorizationService;

class AbstractLvaFormServiceFactory implements AbstractFactoryInterface
{
    public const FORM_SERVICE_CLASS_ALIASES = [
        // Type of Licence
        'lva-licence-type_of_licence' => LicenceTypeOfLicence::class,
        'lva-variation-type_of_licence' => VariationTypeOfLicence::class,
        'lva-application-type_of_licence' => ApplicationTypeOfLicence::class,

        // Address
        'lva-licence-addresses' => LicenceAddresses::class,
        'lva-variation-addresses' => VariationAddresses::class,
        'lva-application-addresses' => ApplicationAddresses::class,

        // Safety
        'lva-licence-safety' => LicenceSafety::class,
        'lva-variation-safety' => VariationSafety::class,

        // Operating Centres
        'lva-licence-operating_centres' => LicenceOperatingCentres::class,
        'lva-variation-operating_centres' => VariationOperatingCentres::class,
        'lva-application-operating_centres' => ApplicationOperatingCentres::class,

        'lva-application-operating_centre' => LvaOperatingCentre::class,
        'lva-licence-operating_centre' => LvaOperatingCentre::class,
        'lva-variation-operating_centre' => LvaOperatingCentre::class,

        // Business Type
        'lva-application-business_type' => ApplicationBusinessType::class,
        'lva-licence-business_type' => LicenceBusinessType::class,
        'lva-variation-business_type' => VariationBusinessType::class,

        'lva-lock-business_details' => LockBusinessDetails::class,
        'lva-licence-business_details' => LicenceBusinessDetails::class,
        'lva-variation-business_details' => VariationBusinessDetails::class,
        'lva-application-business_details' => ApplicationBusinessDetails::class,

        // Goods vehicle filter form service
        'lva-application-goods-vehicles-filters' => ApplicationGoodsVehiclesFilters::class,

        // External common goods vehicles vehicle form service
        'lva-application-goods-vehicles-add-vehicle' => GoodsVehicles\AddVehicle::class,
        'lva-licence-vehicles_psv' => LicencePsvVehicles::class,
        'lva-licence-goods-vehicles' => LicenceGoodsVehicles::class,
        'lva-licence-goods-vehicles-add-vehicle' => GoodsVehicles\AddVehicle::class,
        'lva-variation-goods-vehicles-add-vehicle' => GoodsVehicles\AddVehicle::class,
        'lva-application-goods-vehicles-edit-vehicle' => GoodsVehicles\EditVehicle::class,
        'lva-licence-goods-vehicles-edit-vehicle' => GoodsVehicles\EditVehicle::class,
        'lva-variation-goods-vehicles-edit-vehicle' => GoodsVehicles\EditVehicle::class,

        // External common psv vehicles vehicle form service
        'lva-psv-vehicles-vehicle' => PsvVehiclesVehicle::class,

        // External common vehicles vehicle form service (Goods and PSV)
        'lva-vehicles-vehicle' => VehiclesVehicle::class,

        'lva-application-people' => ApplicationPeople::class,
        'lva-application-financial_evidence' => ApplicationFinancialEvidence::class,
        'lva-application-vehicles_declarations' => ApplicationVehiclesDeclarations::class,
        'lva-application-safety' => ApplicationSafety::class,
        'lva-application-financial_history' => ApplicationFinancialHistory::class,
        'lva-application-licence_history' => ApplicationLicenceHistory::class,
        'lva-application-convictions_penalties' => ApplicationConvictionsPenalties::class,
        'lva-licence-convictions_penalties' => ConvictionsPenalties::class,

        'lva-application-vehicles_psv' => ApplicationPsvVehicles::class,
        'lva-application-goods-vehicles' => ApplicationGoodsVehicles::class,

        'lva-licence-sole_trader' => LicenceSoleTrader::class,
        'lva-variation-sole_trader' => VariationSoleTrader::class,
        'lva-application-sole_trader' => ApplicationSoleTrader::class,

        'lva-application-transport_managers' => ApplicationTransportManager::class,

        'lva-application-taxi_phv' => ApplicationTaxiPhv::class,

        'lva-licence-trailers' => LicenceTrailers::class,
    ];

    public function canCreate($container, $requestedName): bool
    {
        return in_array($requestedName, self::FORM_SERVICE_CLASS_ALIASES);
    }

    public function __invoke($container, $requestedName, array $options = null)
    {
        /** @var FormServiceManager $formServiceLocator */
        /** @var FormHelperService $formHelper */
        /** @var AuthorizationService $authService */
        /** @var UrlHelperService $urlHelper */
        /** @var TranslationHelperService $translator */
        /** @var TableBuilder $tableBuilder */

        $serviceLocator = $container;
        $formHelper = $serviceLocator->get(FormHelperService::class);

        switch ($requestedName) {
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-type_of_licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceTypeOfLicence($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-type_of_licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationTypeOfLicence($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-type_of_licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $guidanceHelper = $serviceLocator->get(GuidanceHelperService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationTypeOfLicence($formHelper, $authService, $guidanceHelper, $formServiceLocator);

            // Address
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-addresses']:
                return new LicenceAddresses($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-addresses']:
                return new VariationAddresses($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-addresses']:
                return new ApplicationAddresses($formHelper);

            // Safety
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-safety']:
                return new LicenceSafety($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-safety']:
                return new VariationSafety($formHelper);

            // Operating Centres
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
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-operating_centres']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $tableBuilder = $serviceLocator->get(TableFactory::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationOperatingCentres($formHelper, $authService, $tableBuilder, $formServiceLocator);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-operating_centre']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-operating_centre']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-operating_centre']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                return new LvaOperatingCentre($formHelper, $translator, $urlHelper);

            // Business Type
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

            case self::FORM_SERVICE_CLASS_ALIASES['lva-lock-business_details']:
                return new LockBusinessDetails($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceBusinessDetails($formHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new VariationBusinessDetails($formHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationBusinessDetails($formHelper, $formServiceLocator);
            // Goods vehicle filter form service
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles-filters']:
                return new ApplicationGoodsVehiclesFilters();

            // External common goods vehicles vehicle form service
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-goods-vehicles-add-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles-add-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles-add-vehicle']:
                return new GoodsVehicles\AddVehicle($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-vehicles_psv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicencePsvVehicles($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicenceGoodsVehicles($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-goods-vehicles-edit-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles-edit-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles-edit-vehicle']:
                return new GoodsVehicles\EditVehicle($formHelper);

            // External common psv vehicles vehicle form service
            case self::FORM_SERVICE_CLASS_ALIASES['lva-psv-vehicles-vehicle']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new PsvVehiclesVehicle($formHelper, $formServiceLocator);

            // External common vehicles vehicle form service (Goods and PSV)
            case self::FORM_SERVICE_CLASS_ALIASES['lva-vehicles-vehicle']:
                return new VehiclesVehicle($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-people']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationPeople($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-financial_evidence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new ApplicationFinancialEvidence($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_declarations']:
                return new ApplicationVehiclesDeclarations($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-safety']:
                return new ApplicationSafety($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-financial_history']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                return new ApplicationFinancialHistory($formHelper, $translator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-licence_history']:
                return new ApplicationLicenceHistory($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-convictions_penalties']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                return new ApplicationConvictionsPenalties($formHelper, $translator, $urlHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-convictions_penalties']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                return new ConvictionsPenalties($formHelper, $translator, $urlHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_psv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationPsvVehicles($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationGoodsVehicles($formHelper, $authService, $formServiceLocator);

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

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-transport_managers']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationTransportManager($formHelper, $authService);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-taxi_phv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationTaxiPhv($formHelper, $authService);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-trailers']:
                return new LicenceTrailers($formHelper);
        }

        // Factory should have been able to satisfy this request but nothing was returned yet, so throw an exception
        throw new InvalidServiceException(sprintf(
            'AbstractLvaFormServiceFactory claimed to be able to supply instance of type "%s", but nothing was returned',
            $requestedName
        ));
    }
}
