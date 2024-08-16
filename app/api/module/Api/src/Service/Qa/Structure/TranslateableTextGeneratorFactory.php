<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TranslateableTextGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TranslateableTextGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TranslateableTextGenerator
    {
        return new TranslateableTextGenerator(
            $container->get('QaTranslateableTextFactory'),
            $container->get('QaTranslateableTextParameterGenerator')
        );
    }
}
