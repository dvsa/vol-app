<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountryAnswerSaver;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ThirdCountryQuestionHandlerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return FixedAnswerQuestionHandler
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FixedAnswerQuestionHandler
    {
        return new FixedAnswerQuestionHandler(
            $container->get('QaGenericAnswerWriter'),
            ThirdCountryAnswerSaver::YES_ANSWER
        );
    }
}
