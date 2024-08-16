<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\CpidOrganisationExport;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Cpid Organisation Export Factory
 *
 * @author Josh Curtis <josh@josh-curtis.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CpidOrganisationExportFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CpidOrganisationExport
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CpidOrganisationExport
    {
        return new CpidOrganisationExport(
            $container->get(AbstractConsumerServices::class),
            $container->get('RepositoryServiceManager')->get('Organisation')
        );
    }
}
