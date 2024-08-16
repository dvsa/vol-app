<?php

namespace Dvsa\Olcs\Api\Service\Qa\PostSubmit;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IrhpApplicationPostSubmitHandlerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpApplicationPostSubmitHandler
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationPostSubmitHandler
    {
        return new IrhpApplicationPostSubmitHandler(
            $container->get('RepositoryServiceManager')->get('IrhpPermit')
        );
    }
}
