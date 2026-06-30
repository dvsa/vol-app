<?php

namespace CommonTest\Common\Util\Stub;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class ServiceWithFactoryStub implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ServiceWithFactoryStub
    {
        return $this;
    }
}
