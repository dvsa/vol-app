<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JsonDecodingFilteredTranslateableTextGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return JsonDecodingFilteredTranslateableTextGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): JsonDecodingFilteredTranslateableTextGenerator
    {
        return new JsonDecodingFilteredTranslateableTextGenerator(
            $container->get('QaFilteredTranslateableTextGenerator')
        );
    }
}
