<?php

namespace Olcs\View\Helper;

use Common\Service\Table\Formatter\FormatterPluginManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Common\Service\Table\Formatter\Address as AddressFormatter;

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
        $addressFormatter = $container->get(FormatterPluginManager::class)->get(AddressFormatter::class);
        return new Address($addressFormatter);
    }
}
