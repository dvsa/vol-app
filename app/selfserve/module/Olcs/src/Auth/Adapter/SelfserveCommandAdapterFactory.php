<?php
declare(strict_types=1);

namespace Olcs\Auth\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SelfserveCommandAdapterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SelfserveCommandAdapter
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SelfserveCommandAdapter
    {
        $commandSender = $container->get('CommandSender');
        return new SelfserveCommandAdapter($commandSender);
    }
}
