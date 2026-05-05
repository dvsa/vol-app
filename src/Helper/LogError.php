<?php

namespace Olcs\Logging\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareTrait;

class LogError implements FactoryInterface
{
    use LoggerAwareTrait;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LogError
    {
        $this->setLogger($container->get('Logger'));
        return $this;
    }
}
