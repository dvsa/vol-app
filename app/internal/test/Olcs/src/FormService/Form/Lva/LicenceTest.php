<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Licence;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use ZfcRbac\Service\AuthorizationService;

/**
 * Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new Licence($this->formHelper, m::mock(AuthorizationService::class));
    }

    public function testAlterForm()
    {
        $form = m::mock(Form::class);
        $formActions = m::mock(Fieldset::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $mockSave = m::mock();
        $mockSave->shouldReceive('setLabel')->once()->with('internal.save.button');
        $mockSave->shouldReceive('setAttribute')->once()->with('class', 'govuk-button');

        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('get')->with('save')->andReturn($mockSave);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $this->sut->alterForm($form);
    }
}
