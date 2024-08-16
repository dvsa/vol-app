<?php

namespace Dvsa\Olcs\Api\Service\Permits\AnswersSummary;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class MultilateralAnswersSummaryRowsAdderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MultilateralAnswersSummaryRowsAdder
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MultilateralAnswersSummaryRowsAdder
    {
        return new MultilateralAnswersSummaryRowsAdder(
            $container->get('PermitsAnswersSummaryRowFactory'),
            $container->get('ViewRenderer')
        );
    }
}
