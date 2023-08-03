<?php

namespace Olcs\Controller\Factory\Entity;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Entity\ViewController;
use ZfcRbac\Service\AuthorizationService;

class ViewControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ViewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ViewController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);

        return new ViewController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $tableFactory
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ViewController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ViewController
    {
        return $this->__invoke($serviceLocator, ViewController::class);
    }
}
