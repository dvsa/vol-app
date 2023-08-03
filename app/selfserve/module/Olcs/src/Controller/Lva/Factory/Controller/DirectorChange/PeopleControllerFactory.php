<?php

namespace Olcs\Controller\Lva\Factory\Controller\DirectorChange;

use Common\FormService\FormServiceManager;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Olcs\Controller\Lva\DirectorChange\PeopleController;
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
        $formServiceManager = $container->get(FormServiceManager::class);
        $lvaAdapter = $container->get(VariationPeopleAdapter::class);

        return new PeopleController(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $lvaAdapter
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
