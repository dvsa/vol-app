<?php

namespace OlcsTest\Controller;

use Common\Service\Data\Search\Search;
use Olcs\Controller\SearchController;
use Mockery as m;
use Common\Service\Data\Search\SearchType;
use Zend\Stdlib\ArrayObject;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Zend\View\Model\ViewModel;

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

    public function setUp()
    {
        $this->markTestSkipped();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
    }

    public function testIndexAction()
    {
        $postData = ['index' => 'application', 'search' => 'asdf', 'action' => 'search'];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'viewHelperManager' => 'ViewHelperManager',
                'url' => 'Url',
            ]
        );
        $elasticSearch = new \Common\Controller\Plugin\ElasticSearch();

        $mockPluginManager->shouldReceive('get')->with('ElasticSearch', '')->andReturn($elasticSearch);

        $url = $mockPluginManager->get('url');
        $url->shouldReceive('fromRoute');

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $searchFilterForm = new \Zend\Form\Form();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with()->andReturn([]);
        $mockParams->shouldReceive('fromQuery')->with()->andReturn([]);
        $mockParams->shouldReceive('fromRoute')->with('index')->andReturn($postData['index']);
        $mockParams->shouldReceive('fromPost')->withNoArgs()->andReturn($postData);

        $mockContainer = new ArrayObject();
        $mockContainer['search'] ='testQuery';
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');
        $mockSearchForm->shouldReceive('isValid')->andReturn(true);
        $mockSearchForm->shouldReceive('getData')->andReturn($postData);

        $mockSearch = m::mock(Search::class);
        $mockSearch->shouldReceive('setQuery')->andReturnSelf();
        $mockSearch->shouldReceive('setRequest')->andReturnSelf();
        $mockSearch->shouldReceive('setIndex')->with($postData['index'])->andReturnSelf();
        $mockSearch->shouldReceive('setSearch')->with('testQuery')->andReturnSelf();
        $mockSearch->shouldReceive('fetchResultsTable')->andReturn('resultsTable');

        $elasticSearch->setSearchService($mockSearch);

        $mockSearchType = m::mock(SearchType::class);
        $mockSearchType->shouldReceive('getNavigation')->andReturn('navigation');

        $elasticSearch->setSearchTypeService($mockSearchType);

        $mockNavigation = m::mock('\Zend\Navigation\Navigation');
        $elasticSearch->setNavigationService($mockNavigation);

        $mockScript = m::mock();
        $mockScript->shouldReceive('loadFiles')->with(['table-actions'])->once();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with(Search::class)->andReturn($mockSearch);
        $mockSl->shouldReceive('get')->with(SearchType::class)->andReturn($mockSearchType);
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('Script')->andReturn($mockScript);

        $mockRouteMatch = m::mock('Zend\Mvc\Router\RouteMatch');
        $mockRouteMatch->shouldReceive('setParam')->with('index', 'application');
        $mockRouteMatch->shouldReceive('getMatchedRouteName')->andReturn('someroute');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);
        $placeholder->getContainer('searchFilter')->set($searchFilterForm);

        $sut = new SearchController();
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockSl);
        $sut->getRequest()->setMethod('GET');
        $sut->getEvent()->setRouteMatch($mockRouteMatch);
        $elasticSearch->setController($sut);

        $view = $sut->searchAction()->getChildren()[1]->getVariables();
        $this->assertEquals('navigation', $view->indexes);
        $this->assertEquals('resultsTable', $view->results);
    }

    public function testIndexActionWithCrudAction()
    {
        $postData = ['index' => 'application', 'search' => 'asdf', 'action' => 'search'];

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'viewHelperManager' => 'ViewHelperManager',
                'url' => 'Url',
            ]
        );
        $elasticSearch = new \Common\Controller\Plugin\ElasticSearch();

        $mockPluginManager->shouldReceive('get')->with('ElasticSearch', '')->andReturn($elasticSearch);

        $url = $mockPluginManager->get('url');
        $url->shouldReceive('fromRoute');

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = $mockPluginManager->get('viewHelperManager', '');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        $searchFilterForm = new \Zend\Form\Form();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with()->andReturn([]);
        $mockParams->shouldReceive('fromQuery')->with()->andReturn([]);
        $mockParams->shouldReceive('fromRoute')->with('index')->andReturn($postData['index']);
        $mockParams->shouldReceive('fromPost')->withNoArgs()->andReturn($postData);

        $mockContainer = new ArrayObject();
        $mockContainer['search'] ='testQuery';
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');
        $mockSearchForm->shouldReceive('isValid')->andReturn(true);
        $mockSearchForm->shouldReceive('getData')->andReturn($postData);

        $mockSearch = m::mock(Search::class);
        $mockSearch->shouldReceive('setQuery')->andReturnSelf();
        $mockSearch->shouldReceive('setRequest')->andReturnSelf();
        $mockSearch->shouldReceive('setIndex')->with($postData['index'])->andReturnSelf();
        $mockSearch->shouldReceive('setSearch')->with('testQuery')->andReturnSelf();
        $mockSearch->shouldReceive('fetchResultsTable')->andReturn('resultsTable');

        $elasticSearch->setSearchService($mockSearch);

        $mockSearchType = m::mock(SearchType::class);
        $mockSearchType->shouldReceive('getNavigation')->andReturn('navigation');

        $elasticSearch->setSearchTypeService($mockSearchType);

        $mockNavigation = m::mock('\Zend\Navigation\Navigation');
        $elasticSearch->setNavigationService($mockNavigation);

        $mockScript = m::mock();
        $mockScript->shouldReceive('loadFiles')->with(['table-actions'])->once();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with(Search::class)->andReturn($mockSearch);
        $mockSl->shouldReceive('get')->with(SearchType::class)->andReturn($mockSearchType);
        $mockSl->shouldReceive('get')->with('viewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('Navigation')->andReturn($mockNavigation);
        $mockSl->shouldReceive('get')->with('Script')->andReturn($mockScript);

        $mockRouteMatch = m::mock('Zend\Mvc\Router\RouteMatch');
        $mockRouteMatch->shouldReceive('setParam')->with('index', 'application');
        $mockRouteMatch->shouldReceive('getMatchedRouteName')->andReturn('someroute');

        $placeholder->getContainer('headerSearch')->set($mockSearchForm);
        $placeholder->getContainer('searchFilter')->set($searchFilterForm);

        $sut = new SearchController();
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockSl);
        $sut->getRequest()->setMethod('GET');
        $sut->getEvent()->setRouteMatch($mockRouteMatch);
        $elasticSearch->setController($sut);

        $view = $sut->searchAction()->getChildren()[1]->getVariables();
        $this->assertEquals('navigation', $view->indexes);
        $this->assertEquals('resultsTable', $view->results);
    }
}
