<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BilateralNoOfPermitsFormControlStrategyFactory implements FactoryInterface
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
            'bilateral_number_of_permits',
            $container->get('QaBilateralNoOfPermitsElementGenerator'),
            $container->get('QaBilateralNoOfPermitsAnswerSaver'),
            $container->get('QaBilateralNoOfPermitsAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaBilateralNoOfPermitsAnswerSummaryProvider')
        );
    }
}
