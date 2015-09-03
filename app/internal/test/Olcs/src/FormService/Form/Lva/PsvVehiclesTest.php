<?php

/**
 * PSV Vehicles Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\PsvVehicles;

/**
 * PSV Vehicles Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PsvVehiclesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = PsvVehicles::class;

    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        $this->formHelper->shouldReceive('createForm')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'shareInfo');

        $mockForm
            ->shouldReceive('get')
            ->with('form-actions')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setLabel')
                            ->with('internal.save.button')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
