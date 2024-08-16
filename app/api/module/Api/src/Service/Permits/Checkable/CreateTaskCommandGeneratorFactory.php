<?php

namespace Dvsa\Olcs\Api\Service\Permits\Checkable;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CreateTaskCommandGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CreateTaskCommandGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CreateTaskCommandGenerator
    {
        return new CreateTaskCommandGenerator(
            $container->get('PermitsCheckableCreateTaskCommandFactory')
        );
    }
}
