<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\Traits;

use Laminas\Form\ElementInterface;
use Mockery as m;

/**
 * Buttons alterations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait ButtonsAlterations
{
    protected function mockAlterButtons(m\MockInterface $form, m\MockInterface $formHelper, ?m\MockInterface $formActions = null): void
    {
        if ($formActions === null) {
            $formActions = m::mock(ElementInterface::class);
        }
        $formActions
            ->shouldReceive('get')
            ->with('saveAndContinue')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setLabel')
                    ->with('lva.external.save_and_continue.button')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setLabel')
                    ->with('lva.external.save_and_return.link')
                    ->once()
                    ->shouldReceive('removeAttribute')
                    ->with('class')
                    ->once()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'govuk-button govuk-button--secondary')
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
