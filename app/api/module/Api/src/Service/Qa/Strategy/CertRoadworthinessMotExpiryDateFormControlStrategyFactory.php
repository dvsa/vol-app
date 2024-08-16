<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CertRoadworthinessMotExpiryDateFormControlStrategyFactory implements FactoryInterface
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
            'cert_road_mot_expiry_date',
            $container->get('QaCertRoadworthinessMotExpiryDateElementGenerator'),
            $container->get('QaDateAnswerSaver'),
            $container->get('QaGenericAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaDateAnswerSummaryProvider')
        );
    }
}
