<?php

namespace Dvsa\Olcs\Api\Service\Permits\Fees;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EcmtApplicationFeeCommandCreatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return EcmtApplicationFeeCommandCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EcmtApplicationFeeCommandCreator
    {
        return new EcmtApplicationFeeCommandCreator(
            $container->get('RepositoryServiceManager')->get('FeeType'),
            $container->get('CqrsCommandCreator'),
            $container->get('CommonCurrentDateTimeFactory')
        );
    }
}
