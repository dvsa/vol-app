<?php

declare(strict_types=1);

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\FileUploadCount;
use Common\Validator\ValidateIf;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;

class VehiclesDeclarationsEvidenceSmall
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected TranslationHelperService $translator, protected UrlHelperService $urlHelper, protected ValidatorPluginManager $validatorPluginManager)
    {
    }

    public function getForm()
    {
        $form = $this->formHelper->createForm('Lva\VehiclesDeclarationsEvidenceSmall');

        $this->alterForm($form);

        return $form;
    }

    protected function alterForm($form)
    {
        $evidenceFieldset = $form->get('evidence');
        $uploadNowRadio = $evidenceFieldset->get('uploadNowRadio');
        $uploadLaterRadio = $evidenceFieldset->get('uploadLaterRadio');
        $uploadNowRadio->setName('uploadNow');
        $uploadNowRadio->setValueOptions(array_replace(
            $uploadNowRadio->getValueOptions(),
            $uploadLaterRadio->getValueOptions()
        ));
        $uploadNowRadio->setOption('single-radio', false);
        $uploadNowRadio->setAttribute('required', true);
        $evidenceFieldset->remove('uploadLaterRadio');
        $this->formHelper->remove($form, 'evidence->uploadNow');
        $this->formHelper->remove($form, 'evidence->evidenceStatementGuidance');

        $inputFilter = $form->getInputFilter();
        $evidenceInputFilter = $inputFilter->get('evidence');

        $uploadNowInput = $evidenceInputFilter->get('uploadNowRadio');
        $uploadNowInput->setRequired(false);
        $uploadNowInput->setContinueIfEmpty(true);

        $requiredChoiceValidator = $this->validatorPluginManager->get(NotEmpty::class);
        $requiredChoiceValidator->setOptions([
            'messages' => [
                NotEmpty::IS_EMPTY => 'financialEvidence_uploadNow-error',
            ],
        ]);
        $uploadNowInput->getValidatorChain()->attach($requiredChoiceValidator);

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

        return $form;
    }
}
