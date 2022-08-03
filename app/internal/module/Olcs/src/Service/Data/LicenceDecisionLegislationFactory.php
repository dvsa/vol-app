<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Licence;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * LicenceDecisionLegislationFactory
 */
class LicenceDecisionLegislationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return LicenceDecisionLegislation
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceDecisionLegislation
    {
        return new LicenceDecisionLegislation(
            $container->get(AbstractDataServiceServices::class),
            $container->get(Licence::class)
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return LicenceDecisionLegislation
     */
    public function createService(ServiceLocatorInterface $services): LicenceDecisionLegislation
    {
        return $this($services, LicenceDecisionLegislation::class);
    }
}
