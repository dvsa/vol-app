<?php

declare(strict_types=1);

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\FileUploadCount;
use Common\Validator\ValidateIf;
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
        $evidenceFieldset->get('uploadNowRadio')->setName('uploadNow');
        $evidenceFieldset->get('uploadLaterRadio')->setName('uploadNow');
        $this->formHelper->remove($form, 'evidence->uploadNow');

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

        return $form;
    }
}
