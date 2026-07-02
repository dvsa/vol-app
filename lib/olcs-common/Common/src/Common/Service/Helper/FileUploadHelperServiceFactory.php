<?php

namespace Common\Service\Helper;

use Common\Service\AntiVirus\Scan;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FileUploadHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FileUploadHelperService
    {
        return new FileUploadHelperService(
            $container->get('Helper\Url'),
            $container->get(Scan::class)
        );
    }
}
