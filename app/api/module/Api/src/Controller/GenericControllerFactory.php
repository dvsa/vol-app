<?php

namespace Dvsa\Olcs\Api\Controller;

use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\IrhpCandidatePermitsCreator;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Generic Controller Factory
 */
class GenericControllerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpCandidatePermitsCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GenericController
    {
        $queryHandlerManager = $container->get('QueryHandlerManager');
        $commandHandlerManager = $container->get('CommandHandlerManager');

        return new GenericController(
            $queryHandlerManager,
            $commandHandlerManager
        );
    }
}
