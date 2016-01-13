<?php

/**
 * Application Type Of Licence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\ApplicationTypeOfLicence;

/**
 * Application Type Of Licence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationTypeOfLicenceTest extends AbstractLvaFormServiceTestCase
{
    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = m::mock(ApplicationTypeOfLicence::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }


    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        $this->formHelper->shouldReceive('createForm')
            ->andReturn($mockForm);

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

        $mockForm
            ->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('difference')
                    ->once()
                    ->getMock()
            )
            ->once();

        $this->fsm
            ->shouldReceive('get')
            ->with('lva-application')
            ->andReturn(
                m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm)
                    ->andReturn($mockForm)
                    ->getMock()
            );

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
