<?php

namespace Olcs\Service\Data;

use Common\Service\Data\Licence;
use Common\Service\Data\RefDataServices;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ImpoundingLegislationFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return ImpoundingLegislation
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ImpoundingLegislation
    {
        return new ImpoundingLegislation(
            $container->get(RefDataServices::class),
            $container->get(Licence::class)
        );
    }
}
