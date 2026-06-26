<?php

namespace Common\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PageIdFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PageId
    {
        /** @var RouteMatch $routeMatch */
        $routeMatch = $container->get('Application')->getMvcEvent()->getRouteMatch();

        $routeMatchName = 'unknown';
        $action = 'unknown';

        if ($routeMatch !== null) {
            $routeMatchName = $routeMatch->getMatchedRouteName();
            $action = $routeMatch->getParam('action');
        }

        return new PageId(
            $routeMatchName,
            $action
        );
    }
}
