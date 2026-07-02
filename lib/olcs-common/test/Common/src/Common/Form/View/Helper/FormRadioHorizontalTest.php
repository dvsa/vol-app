<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormRadioHorizontal;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\ElementInterface;
use Laminas\View\Renderer\RendererInterface;

class FormRadioHorizontalTest extends TestCase
{
    /**
     * @var FormRadioHorizontal
     */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new FormRadioHorizontal();
    }

    public function testInvoke(): void
    {
        $returnValue = 'string';
        $mockElement = m::mock(ElementInterface::class);
        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('vars->getArrayCopy')->with()->andReturn(['VAR' => 'FOO']);
        $mockView->shouldReceive('render')
            ->with('partials/form/radio-horizontal', ['VAR' => 'FOO', 'element' => $mockElement])
            ->andReturn($returnValue);

        $this->sut->setView($mockView);

        self::assertEquals($returnValue, $this->sut->__invoke($mockElement));
    }
}
