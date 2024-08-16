<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BilateralPermitUsageFormControlStrategyFactory implements FactoryInterface
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
            'bilateral_permit_usage',
            $container->get('QaBilateralPermitUsageGenerator'),
            $container->get('QaBilateralPermitUsageAnswerSaver'),
            $container->get('QaGenericAnswerClearer'),
            $container->get('QaBilateralPermitUsageQuestionTextGenerator'),
            $container->get('QaBilateralPermitUsageAnswerSummaryProvider')
        );
    }
}
