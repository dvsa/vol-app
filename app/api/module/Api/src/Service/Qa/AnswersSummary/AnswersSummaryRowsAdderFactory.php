<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswersSummary;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnswersSummaryRowsAdderFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AnswersSummaryRowsAdder
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnswersSummaryRowsAdder
    {
        return new AnswersSummaryRowsAdder(
            $container->get('QaSupplementedApplicationStepsProvider'),
            $container->get('QaAnswersSummaryRowGenerator')
        );
    }
}
