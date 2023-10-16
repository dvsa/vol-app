<?php

namespace OlcsTest\Controller\Application\Processing;

use Common\Service\Data\PluginManager as DataServiceManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Api\Domain\Repository\DataService;
use Laminas\Mvc\Router\Http\TreeRouteStack;
use Laminas\View\HelperPluginManager;
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
use Common\Service\Data\Application as ApplicationData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

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

        $controller = new ApplicationProcessingOverviewController(
            $mockScriptFactory,
            $mockFormHelper,
            $mockTableFactory,
            $mockViewHelperManager,
            $mockDataServiceManager,
            $mockOppositionHelper,
            $mockComplaintsHelper,
            $mockFlashMessengerHelper,
            $mockRouter
        );

        $serviceManager = Bootstrap::getServiceManager();

        /**
        * @var \Laminas\Mvc\Router\Http\TreeRouteStack $router
        */
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
