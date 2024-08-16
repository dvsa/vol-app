<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcmtRemovalNoOfPermitsFormControlStrategyFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BaseFormControlStrategy
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BaseFormControlStrategy
    {
        return new BaseFormControlStrategy(
            'text',
            $container->get('QaTotAuthVehiclesTextElementGenerator'),
            $container->get('QaEcmtRemovalNoOfPermitsAnswerSaver'),
            $container->get('QaEcmtRemovalNoOfPermitsAnswerClearer'),
            $container->get('QaEcmtRemovalNoOfPermitsQuestionTextGenerator'),
            $container->get('QaGenericAnswerSummaryProvider')
        );
    }
}
