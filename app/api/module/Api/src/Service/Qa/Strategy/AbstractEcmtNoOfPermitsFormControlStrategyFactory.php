<?php

namespace Dvsa\Olcs\Api\Service\Qa\Strategy;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AbstractEcmtNoOfPermitsFormControlStrategyFactory implements FactoryInterface
{
    protected $frontendComponent = 'changeMe';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BaseFormControlStrategy
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BaseFormControlStrategy
    {
        return new BaseFormControlStrategy(
            $this->frontendComponent,
            $container->get('QaEcmtNoOfPermitsElementGenerator'),
            $container->get('QaEcmtNoOfPermitsAnswerSaver'),
            $container->get('QaEcmtNoOfPermitsAnswerClearer'),
            $container->get('QaQuestionTextGenerator'),
            $container->get('QaEcmtNoOfPermitsAnswerSummaryProvider')
        );
    }
}
