<?php

namespace Dvsa\Olcs\Api\Service\Qa\AnswersSummary;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AnswersSummaryRowGeneratorFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AnswersSummaryRowGenerator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AnswersSummaryRowGenerator
    {
        return new AnswersSummaryRowGenerator(
            $container->get('PermitsAnswersSummaryRowFactory'),
            $container->get('ViewRenderer'),
            $container->get('QaContextFactory'),
            $container->get('QaElementGeneratorContextGenerator')
        );
    }
}
