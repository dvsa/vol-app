<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\VehiclesDeclarationsEvidenceSmall;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\FileUploadCount;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\VehiclesDeclarationsEvidenceLarge;

#[\PHPUnit\Framework\Attributes\CoversClass(VehiclesDeclarationsEvidenceSmall::class)]
final class VehiclesDeclarationsEvidenceSmallTest extends MockeryTestCase
{
    private VehiclesDeclarationsEvidenceSmall $sut;

    private m\MockInterface $formHelper;

    private m\MockInterface $validatorPluginManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->validatorPluginManager = m::mock(ValidatorPluginManager::class);

        $this->sut = new VehiclesDeclarationsEvidenceSmall(
            $this->formHelper,
            m::mock(AuthorizationService::class),
            m::mock(TranslationHelperService::class),
            m::mock(UrlHelperService::class),
            $this->validatorPluginManager
        );
    }

    public function testGetForm(): void
    {
        $request = m::mock(Request::class);

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

        $validateIfValidator = m::mock(ValidateIf::class);
        $validateIfValidator->expects('setOptions')->with([
            'context_field' => 'uploadNowRadio',
            'context_values' => ['1'],
            'validators' => [
                [
                    'name' => FileUploadCount::class,
                    'options' => [
                        'min' => 1,
                        'message' => 'lva-financial-evidence-upload.required',
                    ],
                ],
            ],
        ]);

        $requiredChoiceValidator = m::mock(NotEmpty::class);
        $requiredChoiceValidator->expects('setOptions')->with([
            'messages' => [
                NotEmpty::IS_EMPTY => 'financialEvidence_uploadNow-error',
            ],
        ]);

        $this->validatorPluginManager->expects('get')
            ->with(NotEmpty::class)
            ->andReturn($requiredChoiceValidator)
            ->once();
        $this->validatorPluginManager->expects('get')
            ->with(ValidateIf::class)
            ->andReturn($validateIfValidator)
            ->once();

        $fileCountInput = m::mock(InputInterface::class);
        $fileCountInput->expects('getValidatorChain->attach')->with($validateIfValidator);

        $uploadNowInput = m::mock(InputInterface::class);
        $uploadNowInput->expects('setRequired')->with(false);
        $uploadNowInput->expects('setContinueIfEmpty')->with(true);
        $uploadNowInput->expects('getValidatorChain->attach')->with($requiredChoiceValidator);

        $evidenceInputFilter = m::mock(InputFilterInterface::class);
        $evidenceInputFilter->expects('get')->with('uploadedFileCount')->andReturn($fileCountInput);
        $evidenceInputFilter->expects('get')->with('uploadNowRadio')->andReturn($uploadNowInput);

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('get')->with('evidence')->andReturn($evidenceInputFilter);

        $form = m::mock(Form::class);
        $form->expects('getInputFilter')->withNoArgs()->andReturn($inputFilter);
        $form->expects('get')->with('evidence')->andReturn($evidenceFieldset);

        $this->formHelper->expects('createForm')
            ->with('Lva\VehiclesDeclarationsEvidenceSmall')
            ->andReturn($form);
        $this->formHelper->expects('remove')->with($form, 'evidence->uploadNow');
        $this->formHelper->expects('remove')->with($form, 'evidence->evidenceStatementGuidance');

        $this->assertSame($form, $this->sut->getForm($request));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('requiredChoiceProvider')]
    public function testUploadChoiceIsRequired($uploadChoice, bool $isValid): void
    {
        $input = new Input();
        $input->setRequired(false);
        $input->setContinueIfEmpty(true);
        $input->getValidatorChain()->attach(new NotEmpty());
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
