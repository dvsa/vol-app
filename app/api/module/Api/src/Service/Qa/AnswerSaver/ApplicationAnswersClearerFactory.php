<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswerSaver;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationAnswersClearerFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationAnswersClearer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationAnswersClearer
    {
        return new ApplicationAnswersClearer(
            $container->get('QaSupplementedApplicationStepsProvider'),
            $container->get('QaContextFactory')
        );
    }
}
