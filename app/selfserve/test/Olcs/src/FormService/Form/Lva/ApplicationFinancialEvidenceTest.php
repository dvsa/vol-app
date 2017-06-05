<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationFinancialEvidence;
use Zend\Form\Form;
use Zend\Http\Request;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Financial Evidence Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationFinancialEvidenceTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationFinancialEvidence
     */
    protected $sut;

    protected $fh;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationFinancialEvidence();
        $this->sut->setFormHelper($this->fh);
    }

    public function testAlterForm()
    {
        $mockSaveButton = m::mock()
            ->shouldReceive('setLabel')
            ->with('lva.external.save_and_return.link')
            ->once()
            ->shouldReceive('removeAttribute')
            ->with('class')
            ->once()
            ->shouldReceive('setAttribute')
            ->with('class', 'action--tertiary large')
            ->once()
            ->getMock();

        $mockFormActions = m::mock()
            ->shouldReceive('get')
            ->with('save')
            ->andReturn($mockSaveButton)
            ->once()
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
            ->shouldReceive('remove')
            ->with('cancel')
            ->once()
            ->getMock();

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($mockFormActions)
            ->once()
            ->shouldReceive('get')
            ->with('evidence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('uploadNowRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('uploadLaterRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('sendByPostRadio')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->times(3)
            ->getMock();

        $mockRequest = m::mock(Request::class);

        $this->fh->shouldReceive('createFormWithRequest')
            ->once()
            ->with('Lva\FinancialEvidence', $mockRequest)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'evidence->uploadNow')
            ->once()
            ->getMock();

        $form = $this->sut->getForm($mockRequest);

        $this->assertSame($mockForm, $form);
    }
}
