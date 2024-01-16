<?php
declare(strict_types=1);

namespace Olcs\Auth\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
}
