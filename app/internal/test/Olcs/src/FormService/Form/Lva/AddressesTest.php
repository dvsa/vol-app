<?php

/**
 * Addresses Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\Addresses;

/**
 * Addresses Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class AddressesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = Addresses::class;

    public function testGetForm()
    {
        // Mocks
        $mockForm = m::mock();

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
            ->shouldReceive('getInputFilter')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('contact')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('get')
                    ->with('email')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('setRequired')
                        ->with(false)
                        ->once()
                        ->shouldReceive('setAllowEmpty')
                        ->with(true)
                        ->once()
                        ->getMock()
                    )
                    ->twice()
                    ->getMock()
                )
                ->twice()
                ->getMock()
            )
            ->twice()
            ->getMock();

        $form = $this->sut->getForm('ltyp_sn');

        $this->assertSame($mockForm, $form);
    }
}
