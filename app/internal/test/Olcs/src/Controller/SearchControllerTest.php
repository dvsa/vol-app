<?php

namespace OlcsTest\Controller;

use Olcs\Controller\SearchController;
use Mockery as m;
use Zend\Stdlib\ArrayObject;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class SearchControllerTest
 * @package OlcsTest\Controller
 */
class SearchControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    public function __construct()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
    }

    public function testIndexActionWithNoData()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'flashMessenger' => 'FlashMessenger', 'redirect' => 'Redirect',
                'viewHelperManager' => 'ViewHelperManager']
        );

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('index')->andReturn('licence');

        $mockFlash = $mockPluginManager->get('flashMessenger', '');
        $mockFlash->shouldReceive('addErrorMessage')->with('Please provide a search term');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->andReturn('redirectResponse');

        $mockContainer = new ArrayObject();
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);

        $sut = new SearchController();
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockSl);

        $this->assertEquals('redirectResponse', $sut->indexAction());
    }

    public function testIndexActionWithPostData()
    {
        $postData = ['index' => 'application', 'search' => 'asdf'];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'viewHelperManager' => 'ViewHelperManager']
        );

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('index')->andReturn('licence');
        $mockParams->shouldReceive('fromPost')->withNoArgs()->andReturn($postData);

        $mockContainer = new ArrayObject();
        $mockContainer['search'] ='testQuery';
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');
        $mockSearchForm->shouldReceive('isValid')->andReturn(true);
        $mockSearchForm->shouldReceive('getData')->andReturn($postData);

        $mockSearch = m::mock('Olcs\Service\Data\Search\Search');
        $mockSearch->shouldReceive('setQuery')->andReturnSelf();
        $mockSearch->shouldReceive('setIndex')->with('licence')->andReturnSelf();
        $mockSearch->shouldReceive('setSearch')->with('testQuery')->andReturnSelf();
        $mockSearch->shouldReceive('getNavigation')->andReturn('navigation');
        $mockSearch->shouldReceive('fetchResultsTable')->andReturn('resultsTable');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Search\Search')->andReturn($mockSearch);
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $mockRouteMatch = m::mock('Zend\Mvc\Router\RouteMatch');
        $mockRouteMatch->shouldReceive('setParam')->with('index', 'application');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);

        $sut = new SearchController();
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockSl);
        $sut->getRequest()->setMethod('POST');
        $sut->getEvent()->setRouteMatch($mockRouteMatch);

        $view = $sut->indexAction()->getChildren()[1]->getVariables();

        $this->assertEquals('navigation', $view->indexes);
        $this->assertEquals('resultsTable', $view->results);
    }

    public function testIndexActionWithSessionData()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'viewHelperManager' => 'ViewHelperManager']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('index')->andReturn('licence');

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $mockContainer = new ArrayObject();
        $mockContainer['search'] ='testQuery';
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);

        $mockSearch = m::mock('Olcs\Service\Data\Search\Search');
        $mockSearch->shouldReceive('setQuery')->andReturnSelf();
        $mockSearch->shouldReceive('setIndex')->with('licence')->andReturnSelf();
        $mockSearch->shouldReceive('setSearch')->with('testQuery')->andReturnSelf();
        $mockSearch->shouldReceive('getNavigation')->andReturn('navigation');
        $mockSearch->shouldReceive('fetchResultsTable')->andReturn('resultsTable');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Search\Search')->andReturn($mockSearch);
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);

        $sut = new SearchController();
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockSl);

        $view = $sut->indexAction()->getChildren()[1]->getVariables();

        $this->assertEquals('navigation', $view->indexes);
        $this->assertEquals('resultsTable', $view->results);
    }
}
