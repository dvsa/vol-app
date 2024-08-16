<?php

namespace Dvsa\Olcs\DvsaAddressService\Service;

use Dvsa\Olcs\DvsaAddressService\Client\DvsaAddressServiceClient;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DvsaAddressServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DvsaAddressService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DvsaAddressService
    {

        $logger = $container->get('Logger');
        $dvsaAddressServiceClient = $container->get(DvsaAddressServiceClient::class);

        return new DvsaAddressService($logger, $dvsaAddressServiceClient);
    }
}
