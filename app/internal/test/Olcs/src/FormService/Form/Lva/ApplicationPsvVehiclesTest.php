<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\ApplicationPsvVehicles;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPsvVehiclesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = ApplicationPsvVehicles::class;

    protected $formName = 'Lva\PsvVehicles';

    public function setUp(): void
    {
        $this->classArgs = [m::mock(AuthorizationService::class)];
        parent::setUp();
    }

    public function testGetForm()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('get->setLabel')
            ->once()
            ->with('internal.save.button');

        // Mocks
        $mockForm = m::mock();

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
