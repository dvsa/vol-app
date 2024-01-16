<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return Licence
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Licence
    {
        return new Licence(
            $container->get(AbstractDataServiceServices::class),
            $container->get('Helper\FlashMessenger')
        );
    }
}
