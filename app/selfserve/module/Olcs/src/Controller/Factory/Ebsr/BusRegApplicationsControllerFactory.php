<?php

namespace Olcs\Controller\Factory\Ebsr;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Ebsr\BusRegApplicationsController;
use ZfcRbac\Service\AuthorizationService;

class BusRegApplicationsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BusRegApplicationsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusRegApplicationsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);

        return new BusRegApplicationsController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $tableFactory,
            $formHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BusRegApplicationsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): BusRegApplicationsController
    {
        return $this->__invoke($serviceLocator, BusRegApplicationsController::class);
    }
}
