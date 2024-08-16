<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BilateralStandardAndCabotageFormControlStrategyFactory implements FactoryInterface
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
            'bilateral_standard_and_cabotage',
            $container->get('QaBilateralStandardAndCabotageElementGenerator'),
            $container->get('QaBilateralStandardAndCabotageAnswerSaver'),
            $container->get('QaGenericAnswerClearer'),
            $container->get('QaBilateralCabotageQuestionTextGenerator'),
            $container->get('QaBilateralStandardAndCabotageAnswerSummaryProvider')
        );
    }
}
