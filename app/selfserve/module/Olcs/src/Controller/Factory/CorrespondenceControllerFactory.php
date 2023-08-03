<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\CorrespondenceController;
use ZfcRbac\Service\AuthorizationService;

class CorrespondenceControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CorrespondenceController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CorrespondenceController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);

        return new CorrespondenceController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $tableFactory,
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CorrespondenceController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): CorrespondenceController
    {
        return $this->__invoke($serviceLocator, CorrespondenceController::class);
    }
}
