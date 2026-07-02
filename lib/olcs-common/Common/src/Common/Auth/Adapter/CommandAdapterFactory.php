<?php

declare(strict_types=1);

namespace Common\Auth\Adapter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class CommandAdapterFactory
 * @see CommandAdapter
 */
class CommandAdapterFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CommandAdapter
    {
        $commandSender = $container->get('CommandSender');
        return new CommandAdapter($commandSender);
    }
}
