<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BilateralThirdCountryFormControlStrategyFactory implements FactoryInterface
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
            'bilateral_third_country',
            $container->get('QaBilateralThirdCountryElementGenerator'),
            $container->get('QaBilateralThirdCountryAnswerSaver'),
            $container->get('QaGenericAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaGenericAnswerSummaryProvider')
        );
    }
}
