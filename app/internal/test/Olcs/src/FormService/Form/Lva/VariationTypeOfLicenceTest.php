<?php

/**
 * Variation Type Of Licence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\VariationTypeOfLicence;

/**
 * Variation Type Of Licence Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class VariationTypeOfLicenceTest extends AbstractLvaFormServiceTestCase
{
    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = m::mock(VariationTypeOfLicence::class)
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

        $licenceType = m::mock();
        $mockForm
            ->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('difference')
                    ->once()
                    ->shouldReceive('get')
                    ->with('licence-type')
                    ->once()
                    ->andReturn($licenceType)
                    ->getMock()
            );

        $this->formHelper
            ->shouldReceive('setCurrentOption')
            ->with($licenceType, 'foo')
            ->once();

        $this->fsm
            ->shouldReceive('get')
            ->with('lva-variation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('alterForm')
                    ->with($mockForm)
                    ->andReturn($mockForm)
                    ->getMock()
            );

        $this->sut->shouldReceive('lockElements');

        $form = $this->sut->getForm(['currentLicenceType' => 'foo']);

        $this->assertSame($mockForm, $form);
    }
}
