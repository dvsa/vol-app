<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Olcs\FormService\Form\Lva\LicenceTrailers;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\FormHelperService;

/**
 * Licence Trailers
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class LicenceTrailersTest extends MockeryTestCase
{
    /**
     * @var LicenceTrailers
     */
    private $sut;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);

        $this->sut = new LicenceTrailers($this->formHelper);
    }

    public function testGetForm(): void
    {
        $mockSaveButton = m::mock()
            ->shouldReceive('setAttribute')
            ->with('class', 'govuk-button')
            ->once()
            ->getMock();

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('table')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('table')
                    ->once()
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setTable')
                            ->with('table')
                            ->once()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->shouldReceive('get')
            ->with('form-actions')
            ->twice()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('save')
                    ->twice()
                    ->andReturn($mockSaveButton)
                    ->getMock()
            )
            ->getMock();

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\Trailers', 'request')
            ->once()
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->saveAndContinue')
            ->once()
            ->shouldReceive('alterElementLabel')
            ->with($mockSaveButton, 'internal.', FormHelperService::ALTER_LABEL_PREPEND)
            ->once()
            ->getMock();

        $this->sut->getForm('request', 'table');
    }
}
