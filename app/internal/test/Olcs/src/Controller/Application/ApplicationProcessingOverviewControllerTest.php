<?php

namespace OlcsTest\Controller\Application\Processing;

use Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController;
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\PluginInterface;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use OlcsTest\Bootstrap;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;

/**
 * Class ApplicationProcessingOverviewControllerTest
 * @package OlcsTest\Controller\Application\Processing
 * @covers Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController
 */
class ApplicationProcessingOverviewControllerTest extends \PHPUnit\Framework\TestCase
{
    public function testIndexActionRedirects()
    {
        $controller = $this->getController('index');

        $redirect = $this->createMock(Redirect::class);
        $redirect->expects(self::once())->method('toRoute');

        $controller->getPluginManager()
            ->setService('redirect', $redirect);

        $controller->indexAction();
    }

    private function getController($action)
    {
        $controller = new ApplicationProcessingOverviewController();

        $serviceManager = Bootstrap::getServiceManager();

        /** @var \Laminas\Mvc\Router\Http\TreeRouteStack $router */
        $router = $serviceManager->get('HttpRouter');
        $routeMatch = new RouteMatch(
            [
                'application' => 'internal',
                'controller' => ApplicationProcessingOverviewController::class,
                'action' => $action
            ]
        );

        $event = new MvcEvent();
        $event->setRouter($router);
        $event->setRouteMatch($routeMatch);

        $pluginManager = new PluginManager();

        $controller->setEvent($event);
        $controller->setPluginManager($pluginManager);
        $controller->setServiceLocator($serviceManager);

        return $controller;
    }
}
