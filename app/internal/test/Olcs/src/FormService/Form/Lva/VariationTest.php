<?php

/**
 * Variation Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Variation;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Variation Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class VariationTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new Variation();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterForm()
    {
        $form = m::mock(Form::class);
        $formActions = m::mock(Fieldset::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $mockSave = m::mock();
        $mockSave->shouldReceive('setLabel')->once()->with('internal.save.button');
        $mockSave->shouldReceive('setAttribute')->once()->with('class', 'action--primary large');

        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('get')->with('save')->andReturn($mockSave);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $this->sut->alterForm($form);
    }
}
