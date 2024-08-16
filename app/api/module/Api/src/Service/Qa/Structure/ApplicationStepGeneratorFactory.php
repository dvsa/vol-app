<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationStepGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationStepGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationStepGenerator
    {
        return new ApplicationStepGenerator(
            $container->get('FormControlServiceManager'),
            $container->get('QaApplicationStepFactory'),
            $container->get('QaElementGeneratorContextGenerator')
        );
    }
}
