<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Factory;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Dvsa\Olcs\Application\Controller\TransportManagersController;

/**
 * @see TransportManagersController
 * @see TransportManagersControllerFactoryTest
 */
class TransportManagersControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed|void
     * @deprecated Use __invoke instead.
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TransportManagersController
    {
        return $this->__invoke($serviceLocator, null);
    }

    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagersController
    {
        $controllerManager = $container;
        assert($controllerManager instanceof ServiceLocatorAwareInterface, 'Expected instance of ServiceLocatorAwareInterface');
        $container = $controllerManager->getServiceLocator();

        $instance = new TransportManagersController();
        $instance = $instance->createService($container);
        return $instance;
    }
}
