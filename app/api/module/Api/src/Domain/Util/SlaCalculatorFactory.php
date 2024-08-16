<?php

namespace Dvsa\Olcs\Api\Domain\Util;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class SlaCalculator
 * @package Dvsa\Olcs\Api\Domain\Util
 */
final class SlaCalculatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SlaCalculator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SlaCalculator
    {
        return new SlaCalculator($container->get(TimeProcessorBuilderInterface::class));
    }
}
