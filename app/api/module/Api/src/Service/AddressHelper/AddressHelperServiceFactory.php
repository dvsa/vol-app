<?php

namespace Dvsa\Olcs\Api\Service\AddressHelper;

use Dvsa\Olcs\Api\Domain\Repository;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AddressHelperServiceFactory implements FactoryInterface
{
    public const ADDRESS_SERVICE_ALIAS = 'AddressService';

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AddressHelperService
    {
        return new AddressHelperService(
            $container->get(static::ADDRESS_SERVICE_ALIAS),
            $container->get('RepositoryServiceManager')->get(Repository\PostcodeEnforcementArea::class),
            $container->get('RepositoryServiceManager')->get(Repository\AdminAreaTrafficArea::class)
        );
    }
}
