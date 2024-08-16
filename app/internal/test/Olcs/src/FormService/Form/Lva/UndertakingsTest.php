<?php

/**
 * Undertakings (Declarations) Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace OlcsTest\FormService\Form\Lva;

use Laminas\Form\ElementInterface;
use Mockery as m;
use Olcs\FormService\Form\Lva\Undertakings;

/**
 * Undertakings (Declarations) Form Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UndertakingsTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = Undertakings::class;

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
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setLabel')
                            ->with('internal.save.button')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $this->formHelper
            ->shouldReceive('remove')
            ->with($mockForm, 'interim')
            ->once();

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
