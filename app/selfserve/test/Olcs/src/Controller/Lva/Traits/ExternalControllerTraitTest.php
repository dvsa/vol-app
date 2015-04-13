<?php

/**
 * External Controller Trait Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Traits;

use OlcsTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * External Controller Trait Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class ExternalControllerTraitTest extends MockeryTestCase
{
    protected $sut;

    protected function setUp()
    {
        $this->sut = m::mock('OlcsTest\Controller\Lva\Traits\Stubs\ExternalControllerTraitStub')
            ->makePartial();
    }

    public function testRenderWithViewModelReturnsViewModel()
    {
        $this->sut->shouldReceive('attachCurrentMessages');

        $view = new \Zend\View\Model\ViewModel();

        $this->assertEquals(
            $view,
            $this->sut->callRender($view)
        );
    }

    public function testRenderWithNormalRequest()
    {
        $this->sut->shouldReceive('attachCurrentMessages')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $view = $this->sut->callRender('my-page');

        $this->assertEquals('layout/layout', $view->getTemplate());
        $this->assertTrue($view->terminate());

        $this->assertEquals(1, $view->count());

        $children = $view->getChildren();

        $this->assertEquals(
            [
                'title' => 'lva.section.title.my-page',
                'form' => null
            ],
            (array)$children[0]->getVariables()
        );
    }

    public function testRenderWithXmlHttpRequest()
    {
        $this->sut->shouldReceive('attachCurrentMessages')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(true)
                ->getMock()
            );

        $view = $this->sut->callRender('my-page');

        $this->assertEquals('layout/ajax', $view->getTemplate());
        $this->assertTrue($view->terminate());
    }
}
