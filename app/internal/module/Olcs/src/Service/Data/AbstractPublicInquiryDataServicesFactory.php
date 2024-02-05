<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataServiceServices;
use Common\Service\Data\Application;
use Common\Service\Data\Licence;
use Common\Service\Data\PluginManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

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
            $container->get(PluginManager::class)->get(Application::class),
            $container->get(PluginManager::class)->get(Licence::class)
        );
    }
}
