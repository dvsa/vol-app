<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TextGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TextGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TextGenerator
    {
        return new TextGenerator(
            $container->get('QaTextElementFactory'),
            $container->get('QaTranslateableTextGenerator')
        );
    }
}
