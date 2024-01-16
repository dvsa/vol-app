<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EcmtNoOfPermitsFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return EcmtNoOfPermits
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EcmtNoOfPermits
    {
        return new EcmtNoOfPermits(
            $container->get(TranslationHelperService::class)
        );
    }
}
