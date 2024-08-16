<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class QuestionTextGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return QuestionTextGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): QuestionTextGenerator
    {
        return new QuestionTextGenerator(
            $container->get('QaQuestionTextFactory'),
            $container->get('QaJsonDecodingFilteredTranslateableTextGenerator')
        );
    }
}
