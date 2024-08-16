<?php

namespace Dvsa\Olcs\Api\Service\GovUkAccount;

use Dvsa\GovUkAccount\Provider\GovUkAccount;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GovUkAccountServiceFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GovUkAccountService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GovUkAccountService
    {
        $config = $container->get('config')['govuk_account'];

        return new GovUkAccountService($config, new GovUkAccount($config));
    }
}
