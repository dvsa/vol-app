<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\RefData;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationFinancialEvidence;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

final class ApplicationFinancialEvidenceTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationFinancialEvidence
     */
    protected $sut;
    /** @var  FormHelperService */
    protected $fh;
    /** @var  m\MockInterface */
    protected $urlHelper;
    /** @var  m\MockInterface */
    protected $translator;

    private $vpm;

    #[\Override]
    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
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

        $uploadNowRadioElement = m::mock(ElementInterface::class)
            ->shouldReceive('setName')->with('uploadNow')->once()
            ->shouldReceive('getValueOptions')->andReturn([
                RefData::AD_UPLOAD_NOW => 'lva-financial-evidence-upload-now.yes',
            ])
            ->shouldReceive('setValueOptions')->with([
                RefData::AD_UPLOAD_NOW => 'lva-financial-evidence-upload-now.yes',
                RefData::AD_UPLOAD_LATER => 'lva-financial-evidence-upload-now.later',
            ])
            ->shouldReceive('setOption')->with('single-radio', false)->once()
            ->shouldReceive('setAttribute')->with('required', true)->once()
            ->getMock();
        $uploadLaterRadioElement = m::mock(ElementInterface::class)
            ->shouldReceive('getValueOptions')->andReturn([
                RefData::AD_UPLOAD_LATER => 'lva-financial-evidence-upload-now.later',
            ])
            ->getMock();
        $evidenceFieldset = m::mock(ElementInterface::class)
            ->shouldReceive('get')->with('uploadNowRadio')->andReturn($uploadNowRadioElement)->once()
            ->shouldReceive('get')->with('uploadLaterRadio')->andReturn($uploadLaterRadioElement)->once()
            ->shouldReceive('remove')->with('uploadLaterRadio')->once()
            ->shouldReceive('setOption')->with('hint', 'BAR')->once()
            ->getMock();

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($mockFormActions)
            ->once()
            ->shouldReceive('get')
            ->with('evidence')
            ->andReturn($evidenceFieldset)
            ->once()
            ->getMock();

        $mockRequest = m::mock(Request::class);

        $mockInputFilter = m::mock(InputFilterInterface::class);
        $mockEvidenceInputFilter = m::mock(InputFilterInterface::class);
        $mockUploadedFileCountInput = m::mock(ElementInterface::class);
        $mockValidatorChain = m::mock(ValidatorChain::class);
        $mockUploadNowInput = m::mock(\Laminas\InputFilter\Input::class);

        $mockForm->shouldReceive('getInputFilter')->andReturn($mockInputFilter);
        $mockInputFilter->shouldReceive('get')->with('evidence')->andReturn($mockEvidenceInputFilter);

        $mockEvidenceInputFilter->shouldReceive('get')->with('uploadNowRadio')->andReturn($mockUploadNowInput);
        $mockUploadNowInput->shouldReceive('setRequired')->with(false)->once();
        $mockUploadNowInput->shouldReceive('setContinueIfEmpty')->with(true)->once();
        $mockUploadNowInput->shouldReceive('getValidatorChain')->andReturn($mockValidatorChain);

        $mockEvidenceInputFilter->shouldReceive('get')->with('uploadedFileCount')->andReturn($mockUploadedFileCountInput);
        $mockUploadedFileCountInput->shouldReceive('getValidatorChain')->andReturn($mockValidatorChain);

        $mockValidateIfValidator = m::mock(ValidateIf::class);
        $mockRequiredChoiceValidator = m::mock(NotEmpty::class);
        $this->vpm->shouldReceive('get')
            ->with(NotEmpty::class)
            ->andReturn($mockRequiredChoiceValidator)
            ->once();
        $this->vpm->shouldReceive('get')
            ->with(ValidateIf::class)
            ->andReturn($mockValidateIfValidator)
            ->once();
        $mockRequiredChoiceValidator->shouldReceive('setOptions')->once();
        $mockValidatorChain->shouldReceive('attach')->with($mockRequiredChoiceValidator)->once();
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
