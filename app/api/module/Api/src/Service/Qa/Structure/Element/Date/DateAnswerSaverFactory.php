<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DateAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DateAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DateAnswerSaver
    {
        return new DateAnswerSaver(
            $container->get('QaGenericAnswerWriter'),
            $container->get('QaGenericAnswerFetcher')
        );
    }
}
