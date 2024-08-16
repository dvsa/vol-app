<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcmtIntJourneysFormControlStrategyFactory implements FactoryInterface
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
            'ecmt_st_international_journeys',
            $container->get('QaEcmtIntJourneysElementGenerator'),
            $container->get('QaEcmtIntJourneysAnswerSaver'),
            $container->get('QaEcmtIntJourneysAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaRadioAnswerSummaryProvider')
        );
    }
}
