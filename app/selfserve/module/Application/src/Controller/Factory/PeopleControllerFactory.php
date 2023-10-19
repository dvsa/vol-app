<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Application\Controller\PeopleController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Adapters\ApplicationPeopleAdapter;
use ZfcRbac\Service\AuthorizationService;

class PeopleControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PeopleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PeopleController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $variationLvaService = $container->get(VariationLvaService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $lvaAdapter = $container->get(ApplicationPeopleAdapter::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new PeopleController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $variationLvaService,
            $guidanceHelper,
            $restrictionHelper,
            $stringHelper,
            $lvaAdapter,
            $flashMessengerHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PeopleController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): PeopleController
    {
        return $this->__invoke($serviceLocator, PeopleController::class);
    }
}
