<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Controller;

use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Laminas\Mvc\Router\RouteMatch;

/**
 * Class ControllerRouteMatchHelper
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ControllerRouteMatchHelper
{
    /**
     * @param array $params
     * @param array $routerConfig
     * @return \Laminas\Mvc\MvcEvent
     */
    public function getMockRouteMatch(array $params = [], array $routerConfig = []): MvcEvent
    {
        $routeMatch = new RouteMatch($params);
        $event      = new MvcEvent();
        $router = HttpRouter::factory($routerConfig);

        $event->setRouter($router);
        $event->setRouteMatch($routeMatch);

        return $event;
    }
}
