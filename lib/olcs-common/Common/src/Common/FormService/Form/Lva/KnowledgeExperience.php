<?php

declare(strict_types=1);

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\Form;
use Laminas\Http\Request;
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

        $inputFilter = $form->getInputFilter();
        $evidenceInputFilter = $inputFilter->get('evidence');

        $evidenceInputFilter->get('uploadNowRadio')->setRequired(false);
        $evidenceInputFilter->get('uploadLaterRadio')->setRequired(false);
    }
}