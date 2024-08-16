<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CommonCertificatesFormControlStrategyFactory implements FactoryInterface
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
            'checkbox',
            $container->get('QaCheckboxElementGenerator'),
            $container->get('QaCommonCertificatesAnswerSaver'),
            $container->get('QaGenericAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaCheckboxAnswerSummaryProvider')
        );
    }
}
