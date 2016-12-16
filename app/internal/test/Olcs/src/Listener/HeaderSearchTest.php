<?php

namespace OlcsTest\Listener;

use Common\Service\Data\Search\Search;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Listener\HeaderSearch;
use Zend\Mvc\MvcEvent;
use \Common\Form\Annotation\CustomAnnotationBuilder;
use Zend\View\Helper\Placeholder;
use Olcs\Form\Element\SearchFilterFieldset;

/**
 * Class HeaderSearchTest
 * @package OlcsTest\Listener
 */
class HeaderSearchTest extends TestCase
{
    /**
     * @var \Olcs\Listener\HeaderSearch
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new HeaderSearch();
    }

    public function testAttach()
    {
        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$this->sut, 'onDispatch'], 20);

        $this->sut->attach($mockEventManager);
    }

    public function testOnDispatch()
    {
        $index = 'licence';

        $params = ['test' => 'value'];
        $mockFab = m::mock('\Common\Form\Annotation\CustomAnnotationBuilder');
        $mockForm = new \Zend\Form\Form();

        $mockSearchService = m::mock(Search::class);
        $mockSearchService->shouldReceive('setIndex')->with($index);
        $mockSearchService->shouldReceive('getFilters')->with([]);
        $this->sut->setSearchService($mockSearchService);

        $formElementManager = m::mock('\Zend\Form\FormElementManager');

        $sff = new SearchFilterFieldset;
        $sff->setName('filter');
        $sff->setSearchService($mockSearchService);
        $formElementManager->shouldReceive('get')
            ->with('SearchFilterFieldset', ['index' => $index, 'name' => 'filter'])
            ->andReturn($sff);

        $srf = new SearchDateRangeFieldset;
        $srf->setName('dateRanges');
        $srf->setSearchService($mockSearchService);
        $formElementManager->shouldReceive('get')
            ->with('SearchDateRangeFieldset', ['index' => $index, 'name' => 'dateRanges'])
            ->andReturn($srf);

        $sof = new SearchOrderFieldset;
        $sof->setName('sort');
        $sof->setSearchService($mockSearchService);
        $formElementManager->shouldReceive('get')
            ->with(SearchOrderFieldset::class, ['index' => $index, 'name' => 'sort'])
            ->andReturn($sof);

        $this->sut->setFormElementManager($formElementManager);

        $mockFab->shouldReceive('createForm')->with('Olcs\\Form\\Model\\Form\\HeaderSearch')->andReturn($mockForm);
        $this->sut->setFormAnnotationBuilder($mockFab);
        $mockFab->shouldReceive('createForm')->with('Olcs\\Form\\Model\\Form\\SearchFilter')->andReturn($mockForm);
        $this->sut->setFormAnnotationBuilder($mockFab);

        $placeholder = new Placeholder();
        $placeholder->getContainer('headerSearch')->set('foobar');

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockEvent = m::mock('Zend\Mvc\MvcEvent');
        $mockEvent->shouldReceive('getRouteMatch')->andReturnSelf();
        $mockEvent->shouldReceive('getParams')->andReturn($params);
        $mockEvent->shouldReceive('getParam')->with('index')->andReturn($index);

        $this->sut->onDispatch($mockEvent);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $formAnnotationBuilder = new \Common\Form\Annotation\CustomAnnotationBuilder();
        $mockSearchService = m::mock(Search::class);
        $formElementManager = m::mock('\Zend\Form\FormElementManager');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with(Search::class)->andReturn($mockSearchService);
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('FormAnnotationBuilder')->andReturn($formAnnotationBuilder);
        $mockSl->shouldReceive('get')->with('FormElementManager')->andReturn($formElementManager);

        $service = $this->sut->createService($mockSl);

        $this->assertSame($this->sut, $service);
        $this->assertSame($mockViewHelperManager, $this->sut->getViewHelperManager());
        $this->assertSame($formAnnotationBuilder, $this->sut->getFormAnnotationBuilder());

    }

    public function testGetViewHelperManager()
    {
        $this->sut->setViewHelperManager('foo');
        $this->assertEquals('foo', $this->sut->getViewHelperManager());
    }

    public function testSetViewHelperManager()
    {
        $this->assertSame($this->sut->setViewHelperManager('foo'), $this->sut);
        $this->assertEquals('foo', $this->sut->getViewHelperManager());
    }

    public function testGetFormAnnotationBuilder()
    {
        $fab = new CustomAnnotationBuilder();
        $this->sut->setFormAnnotationBuilder($fab);
        $this->assertEquals($fab, $this->sut->getFormAnnotationBuilder());
    }

    public function testSetFormAnnotationBuilder()
    {
        $fab = new CustomAnnotationBuilder();
        $this->assertSame($this->sut->setFormAnnotationBuilder($fab), $this->sut);
        $this->assertEquals($fab, $this->sut->getFormAnnotationBuilder());
    }
}
