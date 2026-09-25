<?php

declare(strict_types=1);

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Validator\FileUploadCount;
use Common\Validator\ValidateIf;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;

class KnowledgeExperience extends AbstractLvaFormService
{
    public function __construct(
        protected FormHelperService $formHelper,
        protected AuthorizationService $authService,
        protected TranslationHelperService $translator,
        protected UrlHelperService $urlHelper,
        protected ValidatorPluginManager $validatorPluginManager
    ) {
    }

    public function getForm(Request $request): Form
    {
        $form = $this->formHelper->createFormWithRequest(
            'Lva\KnowledgeExperience',
            $request
        );

        $this->alterForm($form);

        return $form;
    }

    protected function alterForm($form): void
    {
        $evidenceFieldset = $form->get('evidence');

        $evidenceFieldset->get('uploadNowRadio')->setName('uploadNow');
        $evidenceFieldset->get('uploadLaterRadio')->setName('uploadNow');

        $this->formHelper->remove($form, 'evidence->uploadNow');

        // the shared fieldset's guidance is about bank statements
        $this->formHelper->remove($form, 'evidence->evidenceStatementGuidance');

        $inputFilter = $form->getInputFilter();
        $evidenceInputFilter = $inputFilter->get('evidence');

        $evidenceInputFilter->get('uploadNowRadio')->setRequired(false);
        $evidenceInputFilter->get('uploadLaterRadio')->setRequired(false);

        // replace the fieldset's file count validator rather than adding to it, as its message asks for
        // financial evidence and both would report under the same message key
        $validateIfValidator = $this->validatorPluginManager->get(ValidateIf::class);
        $validateIfValidator->setOptions([
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

        $validatorChain = new ValidatorChain();
        $validatorChain->attach($validateIfValidator);

        $evidenceInputFilter->get('uploadedFileCount')->setValidatorChain($validatorChain);
    }
}
