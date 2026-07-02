<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\CommonLicenceTrailers;
use Common\Service\Helper\FormHelperService;

/**
 * Common Licence Trailers Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommonLicenceTrailersTest extends MockeryTestCase
{
    /**
     * @var CommonLicenceTrailers
     */
    private $sut;

    /**
     * @var FormHelperService
     */
    private $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);

        $this->sut = new CommonLicenceTrailers($this->formHelper);
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
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('save')
                    ->once()
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
            ->getMock();

        $this->sut->getForm('request', 'table');
    }
}
