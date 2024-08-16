<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GenericAnswerFetcherFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GenericAnswerFetcher
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GenericAnswerFetcher
    {
        return new GenericAnswerFetcher(
            $container->get('QaNamedAnswerFetcher')
        );
    }
}
