<?php

namespace OlcsTest\Controller;

use Olcs\Controller\SearchController;
use Mockery as m;
use Zend\Stdlib\ArrayObject;

/**
 * Class SearchControllerTest
 * @package OlcsTest\Controller
 */
class SearchControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexActionWithNoData()
    {
        $mockPluginManager = $this->getMockPluginManager(
            ['params' => 'Params', 'flashMessenger' => 'FlashMessenger', 'redirect' => 'Redirect']
        );

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

        $sut = new SearchController();
        $sut->setSearchForm($mockSearchForm);
        $sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $sut->indexAction());
    }

    public function testIndexActionWithPostData()
    {
        $postData = ['index' => 'application', 'search' => 'asdf'];

        $mockPluginManager = $this->getMockPluginManager(['params' => 'Params']);

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

        $mockRouteMatch = m::mock('Zend\Mvc\Router\RouteMatch');
        $mockRouteMatch->shouldReceive('setParam')->with('index', 'application');

        $sut = new SearchController();
        $sut->setSearchForm($mockSearchForm);
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
        $mockPluginManager = $this->getMockPluginManager(['params' => 'Params']);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('index')->andReturn('licence');

        $mockContainer = new ArrayObject();
        $mockContainer['search'] ='testQuery';
        $mockSearchForm = m::mock('Zend\Form\Form');
        $mockSearchForm->shouldReceive('getObject')->andReturn($mockContainer);
        $mockSearchForm->shouldReceive('setData');

        $mockSearch = m::mock('Olcs\Service\Data\Search\Search');
        $mockSearch->shouldReceive('setQuery')->andReturnSelf();
        $mockSearch->shouldReceive('setIndex')->with('licence')->andReturnSelf();
        $mockSearch->shouldReceive('setSearch')->with('testQuery')->andReturnSelf();
        $mockSearch->shouldReceive('getNavigation')->andReturn('navigation');
        $mockSearch->shouldReceive('fetchResultsTable')->andReturn('resultsTable');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Olcs\Service\Data\Search\Search')->andReturn($mockSearch);

        $sut = new SearchController();
        $sut->setSearchForm($mockSearchForm);
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($mockSl);

        $view = $sut->indexAction()->getChildren()[1]->getVariables();

        $this->assertEquals('navigation', $view->indexes);
        $this->assertEquals('resultsTable', $view->results);
    }

    /**
     * @param $class
     * @return m\MockInterface
     */
    protected function getMockPlugin($class)
    {
        if (strpos($class, '\\') === false) {
            $class = 'Zend\Mvc\Controller\Plugin\\' . $class;
        }

        $mockPlugin = m::mock($class);
        $mockPlugin->shouldReceive('__invoke')->andReturnSelf();
        return $mockPlugin;
    }

    /**
     * @param $plugins
     * @return m\MockInterface|\Zend\Mvc\Controller\PluginManager
     */
    protected function getMockPluginManager($plugins)
    {
        $mockPluginManager = m::mock('Zend\Mvc\Controller\PluginManager');
        $mockPluginManager->shouldReceive('setController');

        foreach ($plugins as $name => $class) {
            $mockPlugin = $this->getMockPlugin($class);
            $mockPluginManager->shouldReceive('get')->with($name, '')->andReturn($mockPlugin);
        }

        return $mockPluginManager;
    }
}
