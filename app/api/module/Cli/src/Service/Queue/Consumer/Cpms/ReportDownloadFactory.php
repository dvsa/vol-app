<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Cpms;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ReportDownloadFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ReportDownload(
            $container->get(AbstractConsumerServices::class),
            $container->get('QueryHandlerManager')
        );
    }
}
