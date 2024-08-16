<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\LicencePsvVehicles;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvVehiclesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = LicencePsvVehicles::class;

    protected $formName = 'Lva\PsvVehicles';

    public function setUp(): void
    {
        $this->classArgs = [m::mock(AuthorizationService::class)];
        parent::setUp();
    }

    public function testGetForm()
    {
        $formActions = m::mock()
            ->shouldReceive('has')
            ->with('save')
            ->once()
            ->andReturn(true)
            ->getMock()
            ->shouldReceive('get')
            ->with('save')
            ->twice()
            ->andReturn(
                m::mock()
                ->shouldReceive('setAttribute')
                ->with('class', 'govuk-button')
                ->once()
                ->getMock()
            )
            ->getMock();

        $formActions->shouldReceive('get->setLabel')->with('internal.save.button');

        // Mocks
        $mockForm = m::mock();

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')
            ->with($this->formName)
            ->andReturn($mockForm)
            ->shouldReceive('alterElementLabel')
            ->once()
            ->getMock();

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
