<?php
declare(strict_types=1);

namespace Olcs\Auth\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class InternalCommandAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return InternalCommandAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InternalCommandAdapter
    {
        $commandSender = $container->get('CommandSender');
        return new InternalCommandAdapter($commandSender);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return InternalCommandAdapter
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): InternalCommandAdapter
    {
        return $this->__invoke($serviceLocator, null);
    }
}
