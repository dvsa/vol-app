<?php

namespace Dvsa\Olcs\Api\Service\Permits\Fees;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DaysToPayIssueFeeProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DaysToPayIssueFeeProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DaysToPayIssueFeeProvider
    {
        return new DaysToPayIssueFeeProvider(
            $container->get('RepositoryServiceManager')->get('SystemParameter')
        );
    }
}
