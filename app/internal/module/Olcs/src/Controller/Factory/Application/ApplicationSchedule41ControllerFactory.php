<?php

namespace Olcs\Controller\Factory\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Application\ApplicationSchedule41Controller;
use ZfcRbac\Service\AuthorizationService;

class ApplicationSchedule41ControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ApplicationSchedule41Controller
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationSchedule41Controller
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new ApplicationSchedule41Controller(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $tableFactory,
            $flashMessengerHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ApplicationSchedule41Controller
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ApplicationSchedule41Controller
    {
        return $this->__invoke($serviceLocator, ApplicationSchedule41Controller::class);
    }
}
