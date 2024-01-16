<?php

namespace OlcsTest\Controller\Application\Processing;

use Common\Service\Data\PluginManager as DataServiceManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Application\Processing\ApplicationProcessingOverviewController;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Router\RouteMatch;
use Laminas\Mvc\MvcEvent;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

class ApplicationProcessingOverviewControllerTest extends MockeryTestCase
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
        $mockScriptFactory = m::mock(ScriptFactory::class);
        $mockFormHelper = m::mock(FormHelperService::class);
        $mockTableFactory = m::mock(TableFactory::class);
        $mockViewHelperManager = m::mock(HelperPluginManager::class);
        $mockDataServiceManager = m::mock(DataServiceManager::class);
        $mockOppositionHelper = m::mock(OppositionHelperService::class);
        $mockComplaintsHelper = m::mock(ComplaintsHelperService::class);
        $mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $mockRouter = m::mock(TreeRouteStack::class);
        $mockNavigation = m::mock('Navigation');

        $controller = new ApplicationProcessingOverviewController(
            $mockScriptFactory,
            $mockFormHelper,
            $mockTableFactory,
            $mockViewHelperManager,
            $mockDataServiceManager,
            $mockOppositionHelper,
            $mockComplaintsHelper,
            $mockFlashMessengerHelper,
            $mockRouter,
            $mockNavigation
        );

        $router = $this->createMock(TreeRouteStack::class);
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

        $pluginManager = new PluginManager($this->createMock(ContainerInterface::class));

        $controller->setEvent($event);
        $controller->setPluginManager($pluginManager);

        return $controller;
    }
}
