<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BilateralNoOfPermitsMoroccoFormControlStrategyFactory implements FactoryInterface
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
            'bilateral_number_of_permits_morocco',
            $container->get('QaBilateralNoOfPermitsMoroccoElementGenerator'),
            $container->get('QaBilateralNoOfPermitsMoroccoAnswerSaver'),
            $container->get('QaBilateralNoOfPermitsAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaBilateralNoOfPermitsMoroccoAnswerSummaryProvider')
        );
    }
}
