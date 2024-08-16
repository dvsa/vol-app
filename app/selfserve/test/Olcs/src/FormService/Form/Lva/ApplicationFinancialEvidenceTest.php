<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationFinancialEvidence;
use Laminas\Form\Form;
use Laminas\Http\Request;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use LmcRbacMvc\Service\AuthorizationService;

class ApplicationFinancialEvidenceTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationFinancialEvidence
     */
    protected $sut;

    /** @var  \Common\FormService\FormServiceManager */
    protected $fsm;
    /** @var  FormHelperService */
    protected $fh;
    /** @var  m\MockInterface */
    protected $urlHelper;
    /** @var  m\MockInterface */
    protected $translator;

    private $vpm;

    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $this->vpm = m::mock(ValidatorPluginManager::class);

        $this->sut = new ApplicationFinancialEvidence($this->fh, m::mock(AuthorizationService::class), $this->translator, $this->urlHelper, $this->vpm);
    }

    public function testAlterForm(): void
    {
        $this->translator
            ->shouldReceive('translateReplace')
            ->with('lva-financial-evidence-evidence.hint', ['FOO'])
            ->andReturn('BAR')
            ->once()
            ->getMock();

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with('guides/guide', ['guide' => 'financial-evidence'], [], true)
            ->andReturn('FOO')
            ->once()
            ->getMock();

        $mockSaveButton = m::mock(ElementInterface::class)
            ->shouldReceive('setLabel')
            ->with('lva.external.save_and_return.link')
            ->once()
            ->shouldReceive('removeAttribute')
            ->with('class')
            ->once()
            ->shouldReceive('setAttribute')
            ->with('class', 'govuk-button govuk-button--secondary')
            ->once()
            ->getMock();

        $mockFormActions = m::mock(ElementInterface::class)
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
                m::mock(ElementInterface::class)
                    ->shouldReceive('get')
                    ->with('uploadNowRadio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('uploadLaterRadio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('get')
                    ->with('sendByPostRadio')
                    ->andReturn(
                        m::mock(ElementInterface::class)
                            ->shouldReceive('setName')
                            ->with('uploadNow')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('setOption')
                    ->with('hint', 'BAR')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $mockRequest = m::mock(Request::class);

        $mockInputFilter = m::mock(InputFilterInterface::class);
        $mockEvidenceInputFilter = m::mock(InputFilterInterface::class);
        $mockUploadedFileCountInput = m::mock(ElementInterface::class);
        $mockValidatorChain = m::mock(ValidatorChain::class);

        $mockForm->shouldReceive('getInputFilter')->andReturn($mockInputFilter);
        $mockInputFilter->shouldReceive('get')->with('evidence')->andReturn($mockEvidenceInputFilter);

        $mockEvidenceInputFilter->shouldReceive('get')->with('uploadNowRadio')->andReturn(m::mock(ElementInterface::class)->shouldReceive('setRequired')->with(false)->getMock());
        $mockEvidenceInputFilter->shouldReceive('get')->with('uploadLaterRadio')->andReturn(m::mock(ElementInterface::class)->shouldReceive('setRequired')->with(false)->getMock());
        $mockEvidenceInputFilter->shouldReceive('get')->with('sendByPostRadio')->andReturn(m::mock(ElementInterface::class)->shouldReceive('setRequired')->with(false)->getMock());

        $mockEvidenceInputFilter->shouldReceive('get')->with('uploadedFileCount')->andReturn($mockUploadedFileCountInput);
        $mockUploadedFileCountInput->shouldReceive('getValidatorChain')->andReturn($mockValidatorChain);

        $mockValidateIfValidator = m::mock(ValidateIf::class);
        $this->vpm->shouldReceive('get')->with(ValidateIf::class)->andReturn($mockValidateIfValidator);
        $mockValidateIfValidator->shouldReceive('setOptions')->once();
        $mockValidatorChain->shouldReceive('attach')->with($mockValidateIfValidator)->once();

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
