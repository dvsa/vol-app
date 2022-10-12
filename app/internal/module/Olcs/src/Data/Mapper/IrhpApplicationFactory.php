<?php

namespace Olcs\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class IrhpApplicationFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpApplication
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : IrhpApplication
    {
        return $this->__invoke($serviceLocator, AnalyticsCookieNamesProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpApplication
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : IrhpApplication
    {
        return new IrhpApplication(
            $container->get('QaApplicationStepsPostDataTransformer')
        );
    }
}
