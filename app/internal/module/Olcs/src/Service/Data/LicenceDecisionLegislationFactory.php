<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Licence;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
}
