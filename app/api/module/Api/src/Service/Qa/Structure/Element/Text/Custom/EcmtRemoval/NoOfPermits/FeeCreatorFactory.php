<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FeeCreatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FeeCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FeeCreator
    {
        return new FeeCreator(
            $container->get('RepositoryServiceManager')->get('FeeType'),
            $container->get('CqrsCommandCreator'),
            $container->get('CommandHandlerManager'),
            $container->get('CommonCurrentDateTimeFactory')
        );
    }
}
