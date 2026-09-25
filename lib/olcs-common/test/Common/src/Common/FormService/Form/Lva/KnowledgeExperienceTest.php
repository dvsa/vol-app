<?php

declare(strict_types=1);

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\Form\Lva\KnowledgeExperience;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\FileUploadCount;
use Common\Validator\ValidateIf;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(KnowledgeExperience::class)]
final class KnowledgeExperienceTest extends MockeryTestCase
{
    private KnowledgeExperience $sut;

    private m\MockInterface $formHelper;

    private m\MockInterface $validatorPluginManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->validatorPluginManager = m::mock(ValidatorPluginManager::class);

        $this->sut = new KnowledgeExperience(
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

        $uploadLaterRadioElement = m::mock(ElementInterface::class);
        $uploadLaterRadioElement->expects('setName')->with('uploadNow');

        $evidenceFieldset = m::mock(FieldsetInterface::class);
        $evidenceFieldset->expects('get')->with('uploadNowRadio')->andReturn($uploadNowRadioElement);
        $evidenceFieldset->expects('get')->with('uploadLaterRadio')->andReturn($uploadLaterRadioElement);

        $validateIfValidator = m::mock(ValidateIf::class);
        $validateIfValidator->expects('setOptions')->with([
            'context_field' => 'uploadNowRadio',
            'context_values' => ['1'],
            'validators' => [
                [
                    'name' => FileUploadCount::class,
                    'options' => [
                        'min' => 1,
                        'message' => 'knowledge-experience-evidence.required',
                    ],
                ],
            ],
        ]);

        $this->validatorPluginManager->expects('get')->with(ValidateIf::class)->andReturn($validateIfValidator);

        $fileCountInput = m::mock(InputInterface::class);
        $fileCountInput->expects('setValidatorChain')->with(m::on(
            function (ValidatorChain $chain) use ($validateIfValidator): bool {
                $validators = $chain->getValidators();

                return count($validators) === 1 && $validators[0]['instance'] === $validateIfValidator;
            }
        ));

        $uploadNowInput = m::mock(InputInterface::class);
        $uploadNowInput->expects('setRequired')->with(false);

        $uploadLaterInput = m::mock(InputInterface::class);
        $uploadLaterInput->expects('setRequired')->with(false);

        $evidenceInputFilter = m::mock(InputFilterInterface::class);
        $evidenceInputFilter->expects('get')->with('uploadedFileCount')->andReturn($fileCountInput);
        $evidenceInputFilter->expects('get')->with('uploadNowRadio')->andReturn($uploadNowInput);
        $evidenceInputFilter->expects('get')->with('uploadLaterRadio')->andReturn($uploadLaterInput);

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('get')->with('evidence')->andReturn($evidenceInputFilter);

        $form = m::mock(Form::class);
        $form->expects('getInputFilter')->withNoArgs()->andReturn($inputFilter);
        $form->expects('get')->with('evidence')->andReturn($evidenceFieldset);

        $this->formHelper->expects('createFormWithRequest')
            ->with('Lva\KnowledgeExperience', $request)
            ->andReturn($form);
        $this->formHelper->expects('remove')->with($form, 'evidence->uploadNow');
        $this->formHelper->expects('remove')->with($form, 'evidence->evidenceStatementGuidance');

        $this->assertSame($form, $this->sut->getForm($request));
    }
}
