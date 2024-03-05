<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Rbac\Service\Permission;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableBuilder;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Olcs\FormService\Form\Lva\GoodsVehicles\AddVehicle;
use Olcs\FormService\Form\Lva\GoodsVehicles\AddVehicleLicence;
use Olcs\FormService\Form\Lva\GoodsVehicles\EditVehicle;
use Olcs\FormService\Form\Lva\GoodsVehicles\EditVehicleLicence;
use Olcs\FormService\Form\Lva\OperatingCentre\LvaOperatingCentre;
use Olcs\FormService\Form\Lva\OperatingCentres\ApplicationOperatingCentres;
use LmcRbacMvc\Service\AuthorizationService;

class AbstractLvaFormFactory implements AbstractFactoryInterface
{
    public const FORM_SERVICE_CLASS_ALIASES =  [
        // Operating Centres
        'lva-application-operating_centres' => ApplicationOperatingCentres::class,
        // Operating Centre
        'lva-application-operating_centre' => LvaOperatingCentre::class,
        'lva-licence-operating_centre' => LvaOperatingCentre::class,
        'lva-variation-operating_centre' => LvaOperatingCentre::class,
        // Goods Vehicles
        'lva-licence-goods-vehicles' => LicenceGoodsVehicles::class,
        'lva-application-goods-vehicles-add-vehicle' => AddVehicle::class,
        'lva-licence-goods-vehicles-add-vehicle' => AddVehicleLicence::class,
        'lva-variation-goods-vehicles-add-vehicle' => AddVehicle::class,
        'lva-application-goods-vehicles-edit-vehicle' => EditVehicle::class,
        'lva-licence-goods-vehicles-edit-vehicle' => EditVehicleLicence::class,
        'lva-variation-goods-vehicles-edit-vehicle' => EditVehicle::class,

        'lva-licence' => Licence::class,
        'lva-variation' => Variation::class,
        'lva-application' => Application::class,

        // Internal common psv vehicles vehicle form service
        'lva-psv-vehicles-vehicle' => PsvVehiclesVehicle::class,

        // Addresses form services
        'lva-licence-addresses' => Addresses::class,
        'lva-variation-addresses' => Addresses::class,
        'lva-application-addresses' => Addresses::class,

        'lva-licence-safety' => Safety::class,
        'lva-variation-safety' => Safety::class,
        'lva-application-safety' => Safety::class,

        'lva-application-people' => ApplicationPeople::class,
        'lva-application-taxi-phv' => ApplicationTaxiPhv::class,

        'lva-licence-financial_history' => FinancialHistory::class,
        'lva-variation-financial_history' => FinancialHistory::class,
        'lva-application-financial_history' => FinancialHistory::class,

        'lva-licence-financial_evidence' => FinancialEvidence::class,
        'lva-variation-financial_evidence' => VariationFinancialEvidence::class,
        'lva-application-financial_evidence' => FinancialEvidence::class,

        'lva-variation-undertakings' => Undertakings::class,
        'lva-application-undertakings' => Undertakings::class,

        'lva-application-licence_history' => LicenceHistory::class,

        'lva-variation-convictions_penalties' => ConvictionsPenalties::class,
        'lva-application-convictions_penalties' => ConvictionsPenalties::class,

        'lva-variation-vehicles_declarations' => VehiclesDeclarations::class,
        'lva-application-vehicles_declarations' => VehiclesDeclarations::class,

        'lva-licence-vehicles_psv' => LicencePsvVehicles::class,
        'lva-application-vehicles_psv' => ApplicationPsvVehicles::class,

        'lva-licence-type-of-licence' => LicenceTypeOfLicence::class,
        'lva-application-type-of-licence' => ApplicationTypeOfLicence::class,
        'lva-variation-type-of-licence' => VariationTypeOfLicence::class,

        'lva-licence-trailers' => LicenceTrailers::class,

        'lva-licence-business_details' => LicenceBusinessDetails::class,
        'lva-variation-business_details' => VariationBusinessDetails::class,
        'lva-application-business_details' => ApplicationBusinessDetails::class,
    ];

    /**
     * @param $container
     * @param $requestedName
     * @return bool
     */
    public function canCreate($container, $requestedName): bool
    {
        return in_array($requestedName, self::FORM_SERVICE_CLASS_ALIASES);
    }

    /**
     * @param $container
     * @param $requestedName
     * @param array|null $options
     */
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
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-operating_centres']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $tableBuilder = $serviceLocator->get('Table');
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationOperatingCentres($formHelper, $authService, $tableBuilder, $formServiceLocator);
            // Operating Centre
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-operating_centre']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-operating_centre']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-operating_centre']:
                return new LvaOperatingCentre($formHelper);
            // Goods Vehicles
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceGoodsVehicles($formHelper, $authService, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-goods-vehicles-add-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles-add-vehicle']:
                return new AddVehicle($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles-add-vehicle']:
                return new AddVehicleLicence($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-goods-vehicles-edit-vehicle']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-goods-vehicles-edit-vehicle']:
                return new EditVehicle($formHelper);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-goods-vehicles-edit-vehicle']:
                return new EditVehicleLicence($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new Licence($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new Variation($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new Application($formHelper, $authService);

            // Internal common psv vehicles vehicle form service
            case self::FORM_SERVICE_CLASS_ALIASES['lva-psv-vehicles-vehicle']:
                return new PsvVehiclesVehicle();

            // Addresses form services
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-addresses']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-addresses']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-addresses']:
                return new Addresses($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-safety']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-safety']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-safety']:
                return new Safety($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-people']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationPeople($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-taxi-phv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationTaxiPhv($formHelper, $authService);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-financial_history']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-financial_history']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-financial_history']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                return new FinancialHistory($formHelper, $translator);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-financial_evidence']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-financial_evidence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new FinancialEvidence($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-financial_evidence']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                $validatorPluginManager = $serviceLocator->get('ValidatorManager');
                return new VariationFinancialEvidence($formHelper, $authService, $translator, $urlHelper, $validatorPluginManager);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-undertakings']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-undertakings']:
                return new Undertakings($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-licence_history']:
                return new LicenceHistory($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-convictions_penalties']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-convictions_penalties']:
                $translator = $serviceLocator->get(TranslationHelperService::class);
                $urlHelper = $serviceLocator->get(UrlHelperService::class);
                return new ConvictionsPenalties($formHelper, $translator, $urlHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_declarations']:
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-vehicles_declarations']:
                return new VehiclesDeclarations($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-vehicles_psv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new LicencePsvVehicles($formHelper, $authService);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-vehicles_psv']:
                $authService = $serviceLocator->get(AuthorizationService::class);
                return new ApplicationPsvVehicles($formHelper, $authService);

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

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-trailers']:
                return new LicenceTrailers($formHelper);

            case self::FORM_SERVICE_CLASS_ALIASES['lva-licence-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new LicenceBusinessDetails($formHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-variation-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new         VariationBusinessDetails($formHelper, $formServiceLocator);
            case self::FORM_SERVICE_CLASS_ALIASES['lva-application-business_details']:
                $formServiceLocator = $serviceLocator->get(FormServiceManager::class);
                return new ApplicationBusinessDetails($formHelper, $formServiceLocator);
        }

        // Factory should have been able to satisfy this request but nothing was returned yet, so throw an exception
        throw new InvalidServiceException(sprintf(
            'FormServiceAbstractFactory claimed to be able to supply instance of type "%s", but nothing was returned',
            $requestedName
        ));
    }
}
