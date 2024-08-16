<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BaseAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BaseAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BaseAnswerSaver
    {
        return new BaseAnswerSaver(
            $container->get('QaGenericAnswerWriter'),
            $container->get('QaGenericAnswerFetcher')
        );
    }
}
