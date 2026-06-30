<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DashboardApplicationLinkFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return DashboardApplicationLink
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $urlHelper = $container->get('Helper\Url');
        return new DashboardApplicationLink($urlHelper);
    }
}
