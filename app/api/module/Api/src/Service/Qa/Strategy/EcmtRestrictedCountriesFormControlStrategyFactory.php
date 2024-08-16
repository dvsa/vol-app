<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcmtRestrictedCountriesFormControlStrategyFactory implements FactoryInterface
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
            'ecmt_st_restricted_countries',
            $container->get('QaEcmtRestrictedCountriesElementGenerator'),
            $container->get('QaEcmtRestrictedCountriesAnswerSaver'),
            $container->get('QaEcmtRestrictedCountriesAnswerClearer'),
            $container->get('QaEcmtRestrictedCountriesQuestionTextGenerator'),
            $container->get('QaEcmtRestrictedCountriesAnswerSummaryProvider')
        );
    }
}
