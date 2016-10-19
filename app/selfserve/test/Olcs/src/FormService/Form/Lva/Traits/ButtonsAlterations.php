<?php

namespace OlcsTest\FormService\Form\Lva\Traits;

use Mockery as m;

/**
 * Buttons alterations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait ButtonsAlterations
{
    protected function mockAlterButtons($form, $formHelper, $formActions = null)
    {
        if ($formActions === null) {
            $formActions = m::mock();
        }
        $formActions
            ->shouldReceive('get')
            ->with('saveAndContinue')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setLabel')
                    ->with('lva.external.save_and_continue.button')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setLabel')
                    ->with('lva.external.save_and_return.link')
                    ->once()
                    ->shouldReceive('removeAttribute')
                    ->with('class')
                    ->once()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'action--tertiary large')
                    ->once()
                    ->getMock()
            )
            ->getMock();

        $form->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($formActions)
            ->getMock();

        $formHelper->shouldReceive('remove')
            ->with($form, 'form-actions->cancel')
            ->once()
            ->getMock();
    }
}
