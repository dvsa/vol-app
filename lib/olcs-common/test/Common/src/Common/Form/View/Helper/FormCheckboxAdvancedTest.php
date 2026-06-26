<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormCheckboxAdvanced;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\ElementInterface;
use Laminas\View\Renderer\RendererInterface;

class FormCheckboxAdvancedTest extends MockeryTestCase
{
    protected FormCheckboxAdvanced $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new FormCheckboxAdvanced();
    }

    public function testInvoke(): void
    {
        $viewContent = 'view content';

        $mockElement = m::mock(ElementInterface::class)
            ->shouldReceive('getOption')
            ->with('content')
            ->andReturn('content')
            ->once()
            ->getMock();
        $mockView = m::mock(RendererInterface::class)->makePartial();
        $mockView->data = 'data';
        $mockView
            ->expects('partial')
            ->with(
                'partials/form/checkbox-advanced',
                ['element' => $mockElement, 'content' => 'content', 'data' => 'data']
            )
            ->andReturn($viewContent)
            ->getMock();

        $this->sut->setView($mockView);
        $this->assertEquals($viewContent, $this->sut->__invoke($mockElement));
    }
}
