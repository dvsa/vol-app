<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\VariationFinancialEvidence;

final class VariationFinancialEvidenceTest extends MockeryTestCase
{
    public $validatorPluginManager;
    /** @var  VariationFinancialEvidence */
    protected $sut;

    /** @var  m\MockInterface|\Common\Service\Helper\FormHelperService */
    protected $formHelper;

    /** @var  m\MockInterface */
    protected $urlHelper;

    /** @var  m\MockInterface */
    protected $translator;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $authService = m::mock(\LmcRbacMvc\Service\AuthorizationService::class);
        $this->validatorPluginManager = m::mock(ValidatorPluginManager::class);

        $sm = new ServiceManager();

        $sm->setService('Helper\Url', $this->urlHelper);
        $sm->setService('Helper\Translation', $this->translator);

        $fsm->shouldReceive('getServiceLocator')->andReturn($sm);
        $this->sut = new VariationFinancialEvidence(
            $this->formHelper,
            $authService,
            $this->translator,
            $this->urlHelper,
            $this->validatorPluginManager
        );
    }

    public function testGetForm(): void
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

        /** @var \Laminas\Http\Request $request */
        $request = m::mock(\Laminas\Http\Request::class);

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $uploadNowRadioElement = m::mock(ElementInterface::class);
        $uploadNowRadioElement->expects('setName')->with('uploadNow');
        $uploadNowRadioElement->expects('getValueOptions')->andReturn([
            RefData::AD_UPLOAD_NOW => 'lva-financial-evidence-upload-now.yes',
        ]);
        $uploadNowRadioElement->expects('setValueOptions')->with([
            RefData::AD_UPLOAD_NOW => 'lva-financial-evidence-upload-now.yes',
            RefData::AD_UPLOAD_LATER => 'lva-financial-evidence-upload-now.later',
        ]);
        $uploadNowRadioElement->expects('setOption')->with('single-radio', false);
        $uploadNowRadioElement->expects('setAttribute')->with('required', true);

        $uploadLaterRadioElement = m::mock(ElementInterface::class);
        $uploadLaterRadioElement->expects('getValueOptions')->andReturn([
            RefData::AD_UPLOAD_LATER => 'lva-financial-evidence-upload-now.later',
        ]);

        $evidenceFieldset = m::mock(FieldsetInterface::class);
        $evidenceFieldset->expects('get')->with('uploadNowRadio')->andReturn($uploadNowRadioElement);
        $evidenceFieldset->expects('get')->with('uploadLaterRadio')->andReturn($uploadLaterRadioElement);
        $evidenceFieldset->expects('remove')->with('uploadLaterRadio');
        $evidenceFieldset->expects('setOption')->with('hint', 'BAR');

        $validateIfValidator = m::mock(ValidateIf::class);
        $validateIfValidator->expects('setOptions')->with(m::type('array'));

        $requiredChoiceValidator = m::mock(NotEmpty::class);
        $requiredChoiceValidator->expects('setOptions')->with(m::type('array'));

        $this->validatorPluginManager->shouldReceive('get')
            ->with(NotEmpty::class)
            ->andReturn($requiredChoiceValidator)
            ->once();
        $this->validatorPluginManager->shouldReceive('get')
            ->with(ValidateIf::class)
            ->andReturn($validateIfValidator)
            ->once();

        $fileCountInput = m::mock(InputInterface::class);
        $fileCountInput->expects('getValidatorChain->attach')->with($validateIfValidator);

        $uploadNowInput = m::mock(\Laminas\InputFilter\Input::class);
        $uploadNowInput->expects('setRequired')->with(false);
        $uploadNowInput->expects('setContinueIfEmpty')->with(true);
        $uploadNowInput->expects('getValidatorChain->attach')->with($requiredChoiceValidator);

        $evidenceInputFilter = m::mock(InputFilterInterface::class);
        $evidenceInputFilter->expects('get')->with('uploadedFileCount')->andReturn($fileCountInput);
        $evidenceInputFilter->expects('get')->with('uploadNowRadio')->andReturn($uploadNowInput);

        $inputFilterInterface = m::mock(InputFilterInterface::class);
        $inputFilterInterface->expects('get')->with('evidence')->andReturn($evidenceInputFilter);

        // Mocks
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $mockForm->expects('getInputFilter')->withNoArgs()->andReturn($inputFilterInterface);
        $mockForm->expects('get')
            ->with('evidence')
            ->andReturn($evidenceFieldset);
        $mockForm->expects('has')
        ->with('form-actions')
        ->andReturn(true);
        $mockForm->expects('get')
        ->with('form-actions')
        ->andReturn($formActions);

        $this->formHelper->shouldReceive('createFormWithRequest')
            ->with('Lva\FinancialEvidence', $request)
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'evidence->uploadNow')
            ->once()
            ->getMock();

        $form = $this->sut->getForm($request);

        $this->assertSame($mockForm, $form);
    }
}
