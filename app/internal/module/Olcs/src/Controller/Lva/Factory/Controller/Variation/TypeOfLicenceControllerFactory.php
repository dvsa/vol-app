<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\TypeOfLicenceController;
use ZfcRbac\Service\AuthorizationService;

class TypeOfLicenceControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TypeOfLicenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeOfLicenceController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $formHelper = $container->get(FormHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('Navigation');

        return new TypeOfLicenceController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $formHelper,
            $stringHelper,
            $navigation
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TypeOfLicenceController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TypeOfLicenceController
    {
        return $this->__invoke($serviceLocator, TypeOfLicenceController::class);
    }
}
