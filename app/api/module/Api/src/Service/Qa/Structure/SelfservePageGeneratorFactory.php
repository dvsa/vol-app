<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SelfservePageGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SelfservePageGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SelfservePageGenerator
    {
        return new SelfservePageGenerator(
            $container->get('QaSelfservePageFactory'),
            $container->get('QaApplicationStepGenerator'),
            $container->get('FormControlServiceManager')
        );
    }
}
