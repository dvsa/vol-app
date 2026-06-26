<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DashboardTmApplicationStatusFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return DashboardTmApplicationStatus
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        return new DashboardTmApplicationStatus($viewHelperManager);
    }
}
