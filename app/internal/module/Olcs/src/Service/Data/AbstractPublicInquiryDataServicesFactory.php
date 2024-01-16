<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Application;
use Common\Service\Data\Licence;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractPublicInquiryDataServicesFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return AbstractPublicInquiryDataServices
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AbstractPublicInquiryDataServices
    {
        return new AbstractPublicInquiryDataServices(
            $container->get(AbstractDataServiceServices::class),
            $container->get(Application::class),
            $container->get(Licence::class)
        );
    }
}
