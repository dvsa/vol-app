<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Licence;
use Common\Service\Data\PluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

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
            $container->get(PluginManager::class)->get(Licence::class)
        );
    }
}
