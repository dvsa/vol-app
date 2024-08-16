<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswerSaver;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GenericAnswerWriterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GenericAnswerWriter
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GenericAnswerWriter
    {
        return new GenericAnswerWriter(
            $container->get('QaGenericAnswerProvider'),
            $container->get('QaAnswerFactory'),
            $container->get('RepositoryServiceManager')->get('Answer')
        );
    }
}
