<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcmtPermitUsageFormControlStrategyFactory implements FactoryInterface
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
            'ecmt_st_permit_usage',
            $container->get('QaRadioElementGenerator'),
            $container->get('QaGenericAnswerSaver'),
            $container->get('QaGenericAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaRadioAnswerSummaryProvider')
        );
    }
}
