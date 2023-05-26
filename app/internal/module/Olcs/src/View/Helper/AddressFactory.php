<?php

namespace Olcs\View\Helper;

use Common\Service\Table\Formatter\FormatterPluginManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class MarkersFactory
 * @package Olcs\View\Helper
 */
class AddressFactory implements \Laminas\ServiceManager\FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return Address
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Address
    {
        return $this->__invoke($serviceLocator, Address::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Address
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Address
    {
        return $container->getServiceLocator()->get(FormatterPluginManager::class)->get(\Common\Service\Table\Formatter\Address::class);
    }
}
