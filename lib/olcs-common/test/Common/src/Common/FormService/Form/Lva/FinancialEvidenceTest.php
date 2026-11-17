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
use Laminas\Form\FormInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
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
        $uploadNowRadioElement->expects('setAttribute')->with('required', true);

        $uploadLaterRadioElement = m::mock(ElementInterface::class);
        $uploadLaterRadioElement->expects('setName')->with('uploadNow');

        $evidenceFieldset = m::mock(FieldsetInterface::class);
        $evidenceFieldset->expects('get')->with('uploadNowRadio')->andReturn($uploadNowRadioElement);
        $evidenceFieldset->expects('get')->with('uploadLaterRadio')->andReturn($uploadLaterRadioElement);
        $evidenceFieldset->expects('setOption')->with('hint', 'BAR');

        $validateIfValidator = m::mock(ValidateIf::class);
        $validateIfValidator->expects('setOptions')->with(m::type('array'));

        $requiredChoiceValidator = m::mock(ValidateIf::class);
        $requiredChoiceValidator->expects('setOptions')->with([
            'context_field' => 'uploadLaterRadio',
            'context_values' => [RefData::AD_UPLOAD_LATER],
            'context_truth' => false,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'financialEvidence_uploadNow-error',
                        ],
                    ],
                ],
            ],
        ]);

        $this->validatorPluginManager->shouldReceive('get')
            ->with(ValidateIf::class)
            ->andReturn($requiredChoiceValidator, $validateIfValidator)
            ->twice();

        $fileCountInput = m::mock(InputInterface::class);
        $fileCountInput->expects('getValidatorChain->attach')->with($validateIfValidator);

        $uploadNowInput = m::mock(\Laminas\InputFilter\Input::class);
        $uploadNowInput->expects('setRequired')->with(false);
        $uploadNowInput->expects('setContinueIfEmpty')->with(true);
        $uploadNowInput->expects('getValidatorChain->attach')->with($requiredChoiceValidator);

        $uploadLaterInput = m::mock(InputInterface::class);
        $uploadLaterInput->expects('setRequired')->with(false);

        $evidenceInputFilter = m::mock(InputFilterInterface::class);
        $evidenceInputFilter->expects('get')->with('uploadedFileCount')->andReturn($fileCountInput);
        $evidenceInputFilter->expects('get')->with('uploadNowRadio')->andReturn($uploadNowInput);
        $evidenceInputFilter->expects('get')->with('uploadLaterRadio')->andReturn($uploadLaterInput);

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
    public function testUploadChoiceIsRequired($uploadNow, $uploadLater, bool $isValid): void
    {
        $validatorChain = new ValidatorChain();
        $validatorChain->attach(new NotEmpty([
            'messages' => [
                NotEmpty::IS_EMPTY => 'financialEvidence_uploadNow-error',
            ],
        ]));

        $requiredChoiceValidator = new ValidateIf();
        $requiredChoiceValidator->setOptions([
            'context_field' => 'uploadLaterRadio',
            'context_values' => [RefData::AD_UPLOAD_LATER],
            'context_truth' => false,
        ]);
        $requiredChoiceValidator->setValidatorChain($validatorChain);

        $input = new Input();
        $input->setRequired(false);
        $input->setContinueIfEmpty(true);
        $input->getValidatorChain()->attach($requiredChoiceValidator);
        $input->setValue($uploadNow);

        $this->assertSame($isValid, $input->isValid(['uploadLaterRadio' => $uploadLater]));
    }

    public static function requiredChoiceProvider(): array
    {
        return [
            'no choice' => [null, null, false],
            'upload now' => [RefData::AD_UPLOAD_NOW, null, true],
            'upload later' => [null, RefData::AD_UPLOAD_LATER, true],
        ];
    }
}
