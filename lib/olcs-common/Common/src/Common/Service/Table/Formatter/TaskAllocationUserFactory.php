<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TaskAllocationUserFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TaskAllocationUser
    {
        $dataHelper = $container->get('Helper\Data');
        return new TaskAllocationUser($dataHelper);
    }
}
