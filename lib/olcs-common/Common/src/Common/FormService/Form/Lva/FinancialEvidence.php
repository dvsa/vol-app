<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\FileUploadCount;
use Common\Validator\ValidateIf;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;

class FinancialEvidence extends AbstractLvaFormService
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected TranslationHelperService $translator, protected UrlHelperService $urlHelper, protected ValidatorPluginManager $validatorPluginManager)
    {
    }

    public function getForm(Request $request): Form
    {
        $form = $this->formHelper->createFormWithRequest('Lva\FinancialEvidence', $request);

        $this->alterForm($form);

        return $form;
    }

    protected function alterForm($form): void
    {
        $evidenceFieldset = $form->get('evidence');
        $evidenceFieldset->get('uploadNowRadio')->setName('uploadNow');
        $evidenceFieldset->get('uploadLaterRadio')->setName('uploadNow');
        $this->formHelper->remove($form, 'evidence->uploadNow');

        $evidenceHint = $this->translator->translateReplace(
            'lva-financial-evidence-evidence.hint',
            [
                $this->urlHelper->fromRoute('guides/guide', ['guide' => 'financial-evidence'], [], true),
            ]
        );
        $evidenceFieldset->setOption('hint', $evidenceHint);

        $inputFilter = $form->getInputFilter();

        $evidenceInputFilter = $inputFilter->get('evidence');

        $evidenceInputFilter->get('uploadNowRadio')->setRequired(false);
        $evidenceInputFilter->get('uploadLaterRadio')->setRequired(false);

        $uploadedFileCountInput = $evidenceInputFilter->get('uploadedFileCount');
        $validateIfValidator = $this->validatorPluginManager->get(ValidateIf::class);
        $validateIfValidator->setOptions([
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

        $uploadedFileCountInput->getValidatorChain()->attach($validateIfValidator);
    }
}
