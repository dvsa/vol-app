<?php

namespace OlcsTest\Controller\Application\Processing;

use Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Controller\Plugin\Params;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;
use OlcsTest\Bootstrap;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;

/**
 * Class ApplicationProcessingOverviewControllerTest
 * @package OlcsTest\Controller\Application\Processing
 * @covers Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController
 */
class ApplicationProcessingOverviewControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexActionRedirects()
    {
        $controller = $this->getController('index');

        $redirect = $this->getMock(Redirect::class, [], [], '');
        $redirect->expects(self::once())->method('toRoute');

        $controller->getPluginManager()
            ->setService('redirect', $redirect);

        $controller->indexAction();
    }

    private function getController($action)
    {
        $controller = new ApplicationProcessingOverviewController();

        $serviceManager = Bootstrap::getServiceManager();

        /** @var \Zend\Mvc\Router\Http\TreeRouteStack $router */
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
