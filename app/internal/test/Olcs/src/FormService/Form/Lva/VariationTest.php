<?php

namespace OlcsTest\FormService\Form\Lva;

use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Variation;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class VariationTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new Variation($this->formHelper, m::mock(AuthorizationService::class));
    }

    public function testAlterForm()
    {
        $form = m::mock(Form::class);
        $formActions = m::mock(Fieldset::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $mockSave = m::mock(ElementInterface::class);
        $mockSave->shouldReceive('setLabel')->once()->with('internal.save.button');
        $mockSave->shouldReceive('setAttribute')->once()->with('class', 'govuk-button');

        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('get')->with('save')->andReturn($mockSave);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $this->sut->alterForm($form);
    }
}
