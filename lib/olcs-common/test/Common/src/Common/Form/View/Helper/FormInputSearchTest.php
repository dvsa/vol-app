<?php

namespace CommonTest\Form\View\Helper;

use Common\Form\View\Helper\FormInputSearch;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\ElementInterface;
use Laminas\View\Renderer\RendererInterface;

class FormInputSearchTest extends TestCase
{
    /**
     * @var FormInputSearch
     */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new FormInputSearch();
    }

    public function testInvoke(): void
    {
        $returnedValue = 'string';

        $mockElement = m::mock(ElementInterface::class);
        $mockView = m::mock(RendererInterface::class);
        $mockView->shouldReceive('vars->getArrayCopy')->withNoArgs()->andReturn(['VAR' => 'FOO']);
        $mockView->expects('render')
            ->with('partials/form/input-search', ['VAR' => 'FOO', 'fieldsetElement' => $mockElement])
            ->andReturn($returnedValue);

        $this->sut->setView($mockView);

        $this->assertEquals($returnedValue, $this->sut->__invoke($mockElement));
    }
}
