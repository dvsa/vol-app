<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Dvsa\Olcs\Cli\Service\Queue\Consumer\RefundInterimFees;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Refund Interim Fees Factory
 */
class RefundInterimFeesFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RefundInterimFees
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RefundInterimFees
    {
        return new RefundInterimFees(
            $container->get(AbstractConsumerServices::class),
            $container->get('RepositoryServiceManager')->get('Fee')
        );
    }
}
