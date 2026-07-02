<?php

namespace CommonTest\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Fieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * GenericConfirmationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GenericConfirmationTest extends TestCase
{
    /**
     * @var \Common\Form\GenericConfirmation
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = m::mock(\Common\Form\GenericConfirmation::class)->makePartial();
        parent::setUp();
    }

    public function testSetSubmitLabel(): void
    {
        $fieldset = m::mock(Fieldset::class);
        $element = m::mock(ElementInterface::class);

        $this->sut->shouldReceive('get')->with('form-actions')->once()->andReturn($fieldset);
        $fieldset->shouldReceive('get')->with('submit')->once()->andReturn($element);
        $element->shouldReceive('setLabel')->with('LABEL')->once();

        $this->sut->setSubmitLabel('LABEL');
    }

    public function testRemoveCancel(): void
    {
        $fieldset = m::mock(Fieldset::class);

        $this->sut->shouldReceive('get')->with('form-actions')->once()->andReturn($fieldset);
        $fieldset->shouldReceive('remove')->with('cancel')->once();

        $this->sut->removeCancel();
    }

    public function testSetMessage(): void
    {
        $fieldset = m::mock(Fieldset::class);
        $element = m::mock(ElementInterface::class);

        $this->sut->shouldReceive('get')->with('messages')->once()->andReturn($fieldset);
        $fieldset->shouldReceive('get')->with('message')->once()->andReturn($element);
        $element->shouldReceive('setLabel')->with('MESSAGE')->once();

        $this->sut->setMessage('MESSAGE');
    }
}
