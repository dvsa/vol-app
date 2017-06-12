<?php

namespace OlcsTest\Listener;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Form\Element\SearchDateRangeFieldset;
use Olcs\Form\Element\SearchFilterFieldset;
use Olcs\Form\Element\SearchOrderFieldset;
use Olcs\Form\Model\Form;
use Olcs\Listener\HeaderSearch;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Placeholder;

/**
 * Class HeaderSearchTest
 * @package OlcsTest\Listener
 */
class HeaderSearchTest extends TestCase
{
    /** @var \Olcs\Listener\HeaderSearch */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockFormHlp;
    /** @var  m\MockInterface | \Zend\ServiceManager\ServiceLocatorInterface */
    private $mockSm;
    /** @var  \Common\Service\Data\Search\Search | m\MockInterface  */
    private $mockSearchSrv;
    /** @var  \Zend\Form\FormElementManager | m\MockInterface  */
    private $mockFormElmMngr;
    /** @var  \Zend\View\HelperPluginManager | m\MockInterface  */
    private $mockViewHlprMngr;

    public function setUp()
    {
        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->mockSearchSrv = m::mock(\Common\Service\Data\Search\Search::class);
        $this->mockFormElmMngr = m::mock(\Zend\Form\FormElementManager::class);
        $this->mockViewHlprMngr = m::mock(\Zend\View\HelperPluginManager::class);

        $this->mockSm = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $this->mockSm
            ->shouldReceive('get')->with('DataServiceManager')->andReturnSelf()
            ->shouldReceive('get')->with('Helper\Form')->andReturn($this->mockFormHlp)
            ->shouldReceive('get')->with(\Common\Service\Data\Search\Search::class)->andReturn($this->mockSearchSrv)
            ->shouldReceive('get')->with('FormElementManager')->andReturn($this->mockFormElmMngr)
            ->shouldReceive('get')->with('ViewHelperManager')->andReturn($this->mockViewHlprMngr);

        $this->sut = new HeaderSearch();
    }

    public function testAttach()
    {
        /** @var \Zend\EventManager\EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(\Zend\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$this->sut, 'onDispatch'], 20);

        $this->sut->attach($mockEventManager);
    }

    public function testOnDispatch()
    {
        $index = 'licence';

        $params = ['test' => 'value'];

        $mockForm = new \Zend\Form\Form();

        $this->mockSearchSrv
            ->shouldReceive('setIndex')->with($index)
            ->shouldReceive('getFilters')->with([]);

        $sff = new SearchFilterFieldset;
        $sff->setName('filter');
        $sff->setSearchService($this->mockSearchSrv);
        $this->mockFormElmMngr->shouldReceive('get')
            ->with('SearchFilterFieldset', ['index' => $index, 'name' => 'filter'])
            ->andReturn($sff);

        $srf = new SearchDateRangeFieldset;
        $srf->setName('dateRanges');
        $srf->setSearchService($this->mockSearchSrv);
        $this->mockFormElmMngr->shouldReceive('get')
            ->with('SearchDateRangeFieldset', ['index' => $index, 'name' => 'dateRanges'])
            ->andReturn($srf);

        $sof = new SearchOrderFieldset;
        $sof->setName('sort');
        $sof->setSearchService($this->mockSearchSrv);
        $this->mockFormElmMngr->shouldReceive('get')
            ->with(SearchOrderFieldset::class, ['index' => $index, 'name' => 'sort'])
            ->andReturn($sof);

        $this->mockFormHlp
            ->shouldReceive('createForm')->with(Form\HeaderSearch::class, false)->andReturn($mockForm)
            ->shouldReceive('createForm')->with(Form\SearchFilter::class, false)->andReturn($mockForm);

        $placeholder = new Placeholder();
        $placeholder->getContainer('headerSearch')->set('foobar');

        $this->mockViewHlprMngr->shouldReceive('get')->with('placeholder')->andReturn($placeholder);

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRouteMatch')->andReturnSelf();
        $mockEvent->shouldReceive('getParams')->andReturn($params);
        $mockEvent->shouldReceive('getParam')->with('index')->andReturn($index);

        $this->sut->createService($this->mockSm);
        $this->sut->onDispatch($mockEvent);
    }

    public function testCreateService()
    {
        $service = $this->sut->createService($this->mockSm);

        $this->assertSame($this->sut, $service);
        $this->assertSame($this->mockViewHlprMngr, $this->sut->getViewHelperManager());
        $this->assertSame($this->mockSearchSrv, $this->sut->getSearchService());
        $this->assertSame($this->mockFormElmMngr, $this->sut->getFormElementManager());
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
}
