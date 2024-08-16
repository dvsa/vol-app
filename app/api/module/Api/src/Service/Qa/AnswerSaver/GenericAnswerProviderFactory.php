<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswerSaver;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GenericAnswerProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GenericAnswerProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GenericAnswerProvider
    {
        return new GenericAnswerProvider(
            $container->get('RepositoryServiceManager')->get('Answer')
        );
    }
}
