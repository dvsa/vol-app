<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ViewGeneratorProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ViewGeneratorProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : ViewGeneratorProvider
    {
        return $this->__invoke($serviceLocator, ViewGeneratorProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ViewGeneratorProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ViewGeneratorProvider
    {
        $viewGeneratorProvider = new ViewGeneratorProvider();
        $viewGeneratorProvider->registerViewGenerator(
            'permits/application/question',
            $container->get('QaIrhpApplicationViewGenerator')
        );
        $viewGeneratorProvider->registerViewGenerator(
            'permits/application/ipa/question',
            $container->get('QaIrhpPermitApplicationViewGenerator')
        );
        return $viewGeneratorProvider;
    }
}
