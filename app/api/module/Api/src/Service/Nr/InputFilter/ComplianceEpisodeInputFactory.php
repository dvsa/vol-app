<?php

namespace Dvsa\Olcs\Api\Service\Nr\InputFilter;

use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Nr\Filter\LicenceNumber;
use Dvsa\Olcs\Api\Service\Nr\Filter\Format\MemberStateCode;
use Dvsa\Olcs\Api\Service\Nr\Filter\Vrm as VrmFilter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ComplianceEpisodeInputFactory
 * @package Dvsa\Olcs\Api\Service\Nr\InputFilter
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ComplianceEpisodeInputFactory implements FactoryInterface
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
        $fm = $container->get('FilterManager');
        $service = new Input('compliance_episode');
        $filterChain = $service->getFilterChain();
        $filterChain
            ->attach($fm->get(VrmFilter::class))
            ->attach($fm->get(LicenceNumber::class))
            ->attach($fm->get(MemberStateCode::class));
        return $service;
    }
}
