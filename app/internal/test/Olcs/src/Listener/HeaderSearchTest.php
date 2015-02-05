<?php

namespace OlcsTest\Listener;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Listener\HeaderSearch;
use Zend\Mvc\MvcEvent;
use \Common\Form\Annotation\CustomAnnotationBuilder;
use Zend\View\Helper\Placeholder;

/**
 * Class HeaderSearchTest
 * @package OlcsTest\Listener
 */
class HeaderSearchTest extends TestCase
{
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
        $params = ['test' => 'value'];
        $mockFab = m::mock('\Common\Form\Annotation\CustomAnnotationBuilder');
        $mockForm = new \Zend\Form\Form();

        $mockFab->shouldReceive('createForm')->with('Olcs\\Form\\Model\\Form\\HeaderSearch')->andReturn($mockForm);
        $this->sut->setFormAnnotationBuilder($mockFab);

        $placeholder = new Placeholder();
        $placeholder->getContainer('headerSearch')->set('foobar');

        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $mockViewHelperManager->shouldReceive('get')->with('placeholder')->andReturn($placeholder);
        $this->sut->setViewHelperManager($mockViewHelperManager);

        $mockEvent = m::mock('Zend\Mvc\MvcEvent');
        $mockEvent->shouldReceive('getRouteMatch->getParams')->andReturn($params);

        $this->sut->onDispatch($mockEvent);
    }

    public function testCreateService()
    {
        $mockViewHelperManager = m::mock('Zend\View\HelperPluginManager');
        $formAnnotationBuilder = new \Common\Form\Annotation\CustomAnnotationBuilder();

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('ViewHelperManager')->andReturn($mockViewHelperManager);
        $mockSl->shouldReceive('get')->with('FormAnnotationBuilder')->andReturn($formAnnotationBuilder);

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

/*    public function testOnDispatch()
    {
        $params = ['test' => 'value'];

        $mockEvent = m::mock('Zend\Mvc\MvcEvent');
        $mockEvent->shouldReceive('getRouteMatch->getParams')->andReturn($params);

        $sut = new RouteParams();

        $matcher = function ($item) use ($params, $sut) {
            if (!($item instanceof RouteParam)) {
                return false;
            }
            if ($item->getValue() != 'value' || $item->getContext() != $params || $item->getTarget() != $sut) {
                return false;
            }

            return true;
        };

        $mockEventManager = m::mock('Zend\EventManager\EventManagerInterface');
        $mockEventManager->shouldIgnoreMissing();
        $mockEventManager->shouldReceive('trigger')->with(RouteParams::EVENT_PARAM . 'test', m::on($matcher))->once();

        $sut->setEventManager($mockEventManager);

        $sut->onDispatch($mockEvent);
    }*/
}
