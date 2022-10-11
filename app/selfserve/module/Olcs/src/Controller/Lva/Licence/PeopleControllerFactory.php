<?php

namespace Olcs\Controller\Lva\Licence;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see PeopleController
 */
class PeopleControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return PeopleController
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : PeopleController
    {
        return $this->__invoke($serviceLocator, PeopleController::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PeopleController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : PeopleController
    {
        return new PeopleController(
            $container->get('translator')
        );
    }
}
