<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableBuilder;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceTrailers;
use Laminas\Form\Form;
use Laminas\Http\Request;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

class LicenceTrailersTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var LicenceTrailers
     */
    protected $sut;

    protected $fh;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new LicenceTrailers($this->fh);
    }

    public function testAlterForm(): void
    {
        $mockRequest = m::mock(Request::class);
        $mockTable = m::mock(TableBuilder::class);

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('table')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('get')
                ->with('table')
                ->andReturn(
                    m::mock(ElementInterface::class)
                    ->shouldReceive('setTable')
                    ->with($mockTable)
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('get')
                ->with('save')
                ->andReturn(
                    m::mock(ElementInterface::class)
                    ->shouldReceive('setAttribute')
                    ->with('class', 'govuk-button')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->getMock();

        $this->fh->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\Trailers', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->saveAndContinue')
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $form = $this->sut->getForm($mockRequest, $mockTable);

        $this->assertSame($mockForm, $form);
    }
}
