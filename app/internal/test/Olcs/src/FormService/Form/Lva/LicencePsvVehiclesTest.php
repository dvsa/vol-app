<?php

/**
 * Licence Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\LicencePsvVehicles;

/**
 * Licence Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvVehiclesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = LicencePsvVehicles::class;

    protected $formName = 'Lva\PsvVehicles';

    public function testGetForm()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('get->setLabel')->with('internal.save.button');

        // Mocks
        $mockForm = m::mock();

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')
            ->with($this->formName)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'shareInfo');

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
