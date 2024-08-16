<?php

namespace Dvsa\Olcs\Api\Service\Nr\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Nr\Filter\Format\SiDates as SiDateFilter;
use Dvsa\Olcs\Api\Service\Nr\Filter\Format\IsExecuted;
use Dvsa\Olcs\Api\Service\Nr\Validator\SiPenaltyImposedDate as ImposedDateValidator;
use Psr\Container\ContainerInterface;

/**
 * Class SeriousInfringementInputFactory
 * @package Dvsa\Olcs\Api\Service\Nr\InputFilter
 */
class SeriousInfringementInputFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Input
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Input
    {
        $service = new Input('serious_infringement');
        $filterChain = $service->getFilterChain();
        $filterChain->attach($container->get('FilterManager')->get(IsExecuted::class));
        $filterChain->attach($container->get('FilterManager')->get(SiDateFilter::class));
        $validatorChain = $service->getValidatorChain();
        $validatorChain->attach($container->get('ValidatorManager')->get(ImposedDateValidator::class));
        return $service;
    }
}
