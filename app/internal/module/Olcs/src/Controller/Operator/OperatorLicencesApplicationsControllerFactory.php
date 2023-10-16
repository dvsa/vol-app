<?php

namespace Olcs\Controller\Operator;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Service\Data\ApplicationStatus;

class OperatorLicencesApplicationsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatorLicencesApplicationsController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $operAppStatusService = $container->get(ApplicationStatus::class);
        assert($operAppStatusService instanceof ApplicationStatus);

        return new OperatorLicencesApplicationsController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $operAppStatusService
        );
    }
    public function createService(ServiceLocatorInterface $serviceLocator): OperatorLicencesApplicationsController
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke(
            $container,
            OperatorLicencesApplicationsController::class
        );
    }
}
