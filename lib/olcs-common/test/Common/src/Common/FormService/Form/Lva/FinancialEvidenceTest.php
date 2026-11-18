<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialEvidence;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\RefData;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\FormService\Form\Lva\FinancialEvidence::class)]
final class FinancialEvidenceTest extends MockeryTestCase
{
    public $validatorPluginManager;
    /** @var  FinancialEvidence */
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
        $authService = m::mock(AuthorizationService::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $this->validatorPluginManager = m::mock(ValidatorPluginManager::class);

        $this->sut = new FinancialEvidence(
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
        $requiredChoiceValidator->expects('setOptions')->with([
            'messages' => [
                NotEmpty::IS_EMPTY => 'financialEvidence_uploadNow-error',
            ],
        ]);

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

    #[\PHPUnit\Framework\Attributes\DataProvider('requiredChoiceProvider')]
    public function testUploadChoiceIsRequired($uploadChoice, bool $isValid): void
    {
        $input = new Input();
        $input->setRequired(false);
        $input->setContinueIfEmpty(true);
        $input->getValidatorChain()->attach(new NotEmpty([
            'messages' => [
                NotEmpty::IS_EMPTY => 'financialEvidence_uploadNow-error',
            ],
        ]));
        $input->setValue($uploadChoice);

        $this->assertSame($isValid, $input->isValid());
    }

    public static function requiredChoiceProvider(): array
    {
        return [
            'no choice' => [null, false],
            'upload now' => [RefData::AD_UPLOAD_NOW, true],
            'upload later' => [RefData::AD_UPLOAD_LATER, true],
        ];
    }
}
