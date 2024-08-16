<?php

namespace Olcs\View\Helper;

use Common\Service\Table\Formatter\FormatterPluginManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Common\Service\Table\Formatter\Address as AddressFormatter;

class AddressFactory implements FactoryInterface
{
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
