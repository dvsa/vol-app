<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PermitUsageGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PermitUsageGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitUsageGenerator
    {
        return new PermitUsageGenerator(
            $container->get('QaRadioElementFactory'),
            $container->get('QaTranslateableTextGenerator'),
            $container->get('QaOptionFactory'),
            $container->get('QaOptionListFactory')
        );
    }
}
