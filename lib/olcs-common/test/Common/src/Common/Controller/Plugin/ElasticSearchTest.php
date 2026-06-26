<?php

declare(strict_types=1);

namespace CommonTest\Controller\Plugin;

use Common\Controller\Plugin\ElasticSearch;
use Common\Service\Data\Search\Search;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Helper\Placeholder\Container;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\Segment;
use Laminas\Router\RouteMatch;
use Laminas\Router\SimpleRouteStack;
use Laminas\View\Model\ViewModel;
use Laminas\Navigation\Page\Mvc as NavigationPage;
use CommonTest\Common\Controller\Plugin\ControllerStub;
use Psr\Container\ContainerInterface;

class ElasticSearchTest extends MockeryTestCase
{
    public $request;
    public $routeMatch;
    public $event;
    public $sm;
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $container;
    public $pm;
    public $mockPlaceholder;
    protected $sut;

    protected $controller;

    protected $mockServiceLocator;


    protected $pluginManagerHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->request = m::mock(Request::class);

        $this->routeMatch = new RouteMatch(['controller' => 'index', 'action' => 'index', 'index' => 'SEARCHINDEX']);
        $this->routeMatch->setMatchedRouteName('testindex');

        $this->event = new MvcEvent();

        $routeStack = new SimpleRouteStack();
        $route = new Segment('/testindex/[:controller/[:action/]]');
        $routeStack->addRoute('testindex', $route);
        $route = new Segment('/dashboard/[:controller/[:action/]]');
        $routeStack->addRoute('dashboard', $route);

        $this->event->setRouter($routeStack);

        $this->event->setRouteMatch($this->routeMatch);
        $this->event->setRequest($this->request);

        $this->sm = m::mock(\Laminas\ServiceManager\ServiceManager::class)
            ->makePartial()
            ->setAllowOverride(true);

        $this->container = m::mock(ContainerInterface::class);
        $this->pm = new PluginManager($this->container);
        $this->pm->setInvokableClass('ElasticSearch', ElasticSearch::class);

        $this->mockPlaceholder = m::mock(Placeholder::class);
        $this->sut = new ControllerStub($this->mockPlaceholder);
        $this->sut->setEvent($this->event);
        $this->sut->setPluginManager($this->pm);
    }

    public function testInvokeOptionsSet(): void
    {
        $options = [
            'container_name' => 'testcontainer',
            'page_route' => 'testroute'
        ];

        $result = $this->sut->pluginInvoke($options);

        $this->assertEquals($result->getContainerName(), $options['container_name']);
        $this->assertEquals($result->getPageRoute(), $options['page_route']);
    }

    public function testInvokeDefaultOptions(): void
    {
        $result = $this->sut->pluginInvoke([]);

        $this->assertEquals($result->getContainerName(), 'global_search');
        $this->assertEquals($result->getPageRoute(), 'testindex');
    }

    public function testProcessSearchData(): void
    {
        self::expectNotToPerformAssertions();

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setAttribute')->with(
            'action',
            m::type('string')
        );
        $mockForm->shouldReceive('setData')->with(m::type('array'));
        $mockForm->shouldReceive('getObject')->andReturn($this->getMockSearchObjectArray());
        $mockForm->shouldReceive('isValid')->andReturn(true);
        $mockForm->shouldReceive('getData')->andReturn(['index' => 'SEARCHINDEX']);

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('getValue')->andReturn($mockForm);

        $this->mockPlaceholder->shouldReceive('getContainer')->with('headerSearch')->andReturn($mockContainer);

        $plugin = $this->sut->getPlugin();

        $plugin->processSearchData();
    }

    public function testGetSearchForm(): void
    {
        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setAttribute')->with(
            'action',
            m::type('string')
        );
        $mockForm->shouldReceive('setData');

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('getValue')->andReturn($mockForm);

        $this->mockPlaceholder->shouldReceive('getContainer')->with('headerSearch')->andReturn($mockContainer);

        $plugin = $this->sut->getPlugin();
        $plugin->setSearchData(['index' => 'SEARCHINDEX', 'search' => 'foo']);

        $result = $plugin->getSearchForm();
        $this->assertSame($result, $mockForm);
    }

    public function testGetFiltersForm(): void
    {
        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setAttribute')->with(
            'action',
            m::type('string')
        );
        $mockForm->shouldReceive('setData');

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('getValue')->andReturn($mockForm);

        $this->mockPlaceholder->shouldReceive('getContainer')->with('searchFilter')->andReturn($mockContainer);

        $plugin = $this->sut->getPlugin();
        $plugin->setSearchData(['index' => 'SEARCHINDEX', 'search' => 'foo']);

        $result = $plugin->getFiltersForm();
        $this->assertSame($result, $mockForm);
    }

    public function testSearchAction(): void
    {
        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('getObject')->andReturn($this->getMockSearchObjectArray());
        $mockForm->shouldReceive('setAttribute')->with(
            'action',
            m::type('string')
        );
        $mockForm->shouldReceive('setData');
        $mockForm->shouldReceive('isValid')->andReturn(true);
        $mockForm->shouldReceive('getData')->andReturn(['index' => 'SEARCHINDEX']);

        $indexes = ['searchindex1', 'searchindex2'];
        $results = ['search-results'];

        $mockSearchTypeService = m::mock('Olcs\Service\Data\Search\SearchType');
        $mockSearchTypeService->shouldReceive('getNavigation')->with(
            m::type('string'),
            m::type('array')
        )->andReturn($indexes);

        $mockQuery = m::mock();
        $mockRequest = m::mock();
        $mockIndex = m::mock();

        $mockQuery->shouldReceive('setRequest')->with(m::type('object'))->andReturn($mockRequest);
        $mockRequest->shouldReceive('setIndex')->with('SEARCHINDEX')->andReturn($mockIndex);
        $mockIndex->shouldReceive('setSearch')->with('SEARCH')->andReturnSelf();

        $mockSearchService = m::mock(Search::class);
        $mockSearchService->shouldReceive('setQuery')->with(m::type('object'))->andReturn($mockQuery);
        $mockSearchService->shouldReceive('fetchResultsTable')->andReturn($results);

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('getValue')->andReturn($mockForm);

        $this->mockPlaceholder->shouldReceive('getContainer')->with('searchFilter')->andReturn($mockContainer);
        $this->mockPlaceholder->shouldReceive('getContainer')->with('headerSearch')->andReturn($mockContainer);

        $plugin = $this->sut->getPlugin();
        $plugin->setSearchData(['index' => 'SEARCHINDEX', 'search' => 'foo']);
        $plugin->setSearchTypeService($mockSearchTypeService);
        $plugin->setSearchService($mockSearchService);

        $resultView = $plugin->searchAction();

        $this->assertEquals($resultView->indexes, $indexes);
        $this->assertEquals($resultView->results, $results);
    }

    public function testSetNavigationCurrentLocation(): void
    {
        $plugin = $this->sut->getPlugin();
        $plugin->navigationId = 'home';

        $mockNavigationService = m::mock(Search::class);
        $mockNavigationService->shouldReceive('findOneBy')->with('id', 'home')->andReturnSelf();
        $mockNavigationService->shouldReceive('setActive');
        $plugin->setNavigationService($mockNavigationService);

        $this->assertTrue($plugin->setNavigationCurrentLocation());
    }

    public function testExtractSearchData(): void
    {
        $plugin = $this->sut->getPlugin();

        $result = $plugin->extractSearchData();

        $this->assertArrayHasKey('index', $result);
        $this->assertEquals($result['index'], 'SEARCHINDEX');
    }

    public function testConfigureNavigation(): void
    {
        $mockSearchTypeService = m::mock('Olcs\Service\Data\Search\SearchType');
        $mockSearchService = m::mock(Search::class);

        $mi = m::mock(\Laminas\Navigation\Navigation::class);
        $mi->shouldReceive('findOneBy')->with('id', 'search-da')->andReturnSelf();
        $mi->shouldReceive('setActive')->with(true)->andReturnNull();

        $mockSearchTypeService->shouldReceive('getNavigation')->with(
            'internal-search',
            ['search' => 'foo']
        )->andReturn($mi);

        $page = m::mock(NavigationPage::class);

        $mi->shouldReceive('findOneBy')->with('id', 'remove-id')->once()->andReturn($page);
        $mi->shouldReceive('removePage')->with($page, true)->once()->andReturnTrue();

        $this->mockPlaceholder->shouldReceive('getContainer')->with('horizontalNavigationContainer')->andReturn(m::mock()->shouldReceive('set')->once()->with($mi)->getMock());

        $plugin = $this->sut->getPlugin();
        $plugin->setSearchTypeService($mockSearchTypeService);
        $plugin->setSearchService($mockSearchService);
        $plugin->setSearchData(['search' => 'foo', 'index' => 'da']);

        new ViewModel();
        $plugin->configureNavigation(['remove-id']);
    }

    /**
     * @return string[]
     *
     * @psalm-return array{index: 'foo', search: 'SEARCH'}
     */
    private function getMockSearchObjectArray(): array
    {
        return [
            'index' => 'foo',
            'search' => 'SEARCH'
        ];
    }

    public function testGenerateResults(): void
    {
        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('getObject')->andReturn($this->getMockSearchObjectArray());

        $mockContainer = m::mock(Container::class);
        $mockContainer->shouldReceive('getValue')->andReturn($mockForm);

        $this->mockPlaceholder->shouldReceive('getContainer')->with('headerSearch')->andReturn($mockContainer);

        $mockSearchTypeService = m::mock('Olcs\Service\Data\Search\SearchType');
        $mockSearchService = m::mock(Search::class);

        $mockQuery = m::mock();
        $mockRequest = m::mock();
        $mockIndex = m::mock();

        $mockQuery->shouldReceive('setRequest')->with(m::type('object'))->andReturn($mockRequest);
        $mockRequest->shouldReceive('setIndex')->with('SEARCHINDEX')->andReturn($mockIndex);
        $mockIndex->shouldReceive('setSearch')->with('SEARCH')->andReturnSelf();

        $mockSearchService->shouldReceive('setQuery')->with(m::type('object'))->andReturn($mockQuery);
        $mockSearchService->shouldReceive('fetchResultsTable')->andReturn('RESULTS');

        $plugin = $this->sut->getPlugin();
        $plugin->setSearchTypeService($mockSearchTypeService);
        $plugin->setSearchService($mockSearchService);

        $view = new ViewModel();
        $result = $plugin->generateResults($view);

        $this->assertEquals($result->results, 'RESULTS');
    }

    public function testGetSetContainerName(): void
    {
        $plugin = $this->sut->pluginInvoke([]);

        $plugin->setContainerName('testContainerName');
        $this->assertEquals('testContainerName', $plugin->getContainerName());
    }

    public function testGetSetSearchData(): void
    {
        $plugin = $this->sut->pluginInvoke([]);

        $plugin->setSearchData('testsearchdata');
        $this->assertEquals('testsearchdata', $plugin->getSearchData());
    }

    public function testGetSetPageRoute(): void
    {
        $plugin = $this->sut->pluginInvoke([]);

        $plugin->setPageRoute('testpageroute');
        $this->assertEquals('testpageroute', $plugin->getPageRoute());
    }
}
