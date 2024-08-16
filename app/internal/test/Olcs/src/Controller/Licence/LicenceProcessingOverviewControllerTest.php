<?php

namespace OlcsTest\Controller\Application\Processing;

use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Router\RouteMatch;
use Olcs\Service\Data\SubCategory;
use Laminas\Mvc\MvcEvent;
use Mockery as m;
use Psr\Container\ContainerInterface;

/**
 * Class LicenceProcessingOverviewControllerTest
 * @package OlcsTest\Controller\Licence\Processing
 * @covers Olcs\Controller\Licence\Processing\LicenceProcessingOverviewController
 */
class LicenceProcessingOverviewControllerTest extends \PHPUnit\Framework\TestCase
{
    protected $mockScriptFactory;
    protected $mockFormHelper;
    protected $mockTableFactory;
    protected $mockViewHelperManager;
    protected $mockOppositionHelper;
    protected $mockComplaintsHelper;
    protected $mockNavigation;
    protected $mockSubCategoryDataService;
    protected $mockFlashMessenger;
    protected $mockRouter;

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
        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockTableFactory = m::mock(TableFactory::class);
        $this->mockViewHelperManager = m::mock(HelperPluginManager::class);
        $this->mockOppositionHelper = m::mock(OppositionHelperService::class);
        $this->mockComplaintsHelper = m::mock(ComplaintsHelperService::class);
        $this->mockNavigation = m::mock(); // Note: No class provided for $navigation, so it'll be a generic mock
        $this->mockSubCategoryDataService = m::mock(SubCategory::class);
        $this->mockFlashMessenger = m::mock(FlashMessengerHelperService::class);
        $this->mockRouter = m::mock(TreeRouteStack::class);

        $controller = new LicenceProcessingOverviewController(
            $this->mockScriptFactory,
            $this->mockFormHelper,
            $this->mockTableFactory,
            $this->mockViewHelperManager,
            $this->mockOppositionHelper,
            $this->mockComplaintsHelper,
            $this->mockNavigation,
            $this->mockSubCategoryDataService,
            $this->mockFlashMessenger,
            $this->mockRouter
        );

        $router = $this->createMock(TreeRouteStack::class);
        $routeMatch = new RouteMatch(
            [
                'application' => 'internal',
                'controller' => LicenceProcessingOverviewController::class,
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
