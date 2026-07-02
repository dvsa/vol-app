<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Laminas\Form\Form;
use Mockery as m;
use Common\FormService\Form\Lva\VariationPsvVehicles;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPsvVehiclesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = VariationPsvVehicles::class;

    protected $formName = 'Lva\PsvVehicles';

    #[\Override]
    protected function setUp(): void
    {
        $this->classArgs = [m::mock(AuthorizationService::class)];
        parent::setUp();
    }

    public function testGetFormWithoutFormActions(): void
    {
        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(false);

        $this->formHelper->shouldReceive('createForm')
            ->with($this->formName)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'shareInfo');

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }

    #[\Override]
    public function testGetForm(): void
    {
        $formActions = m::mock(\Laminas\Form\ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

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
