<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * OperatingCentresForInspectionRequestFactory
 */
class OperatingCentresForInspectionRequestFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return OperatingCentresForInspectionRequest
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatingCentresForInspectionRequest
    {
        return new OperatingCentresForInspectionRequest(
            $container->get(AbstractDataServiceServices::class),
            $container->get('Helper\FlashMessenger')
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return OperatingCentresForInspectionRequest
     */
    public function createService(ServiceLocatorInterface $services): OperatingCentresForInspectionRequest
    {
        return $this($services, OperatingCentresForInspectionRequest::class);
    }
}
