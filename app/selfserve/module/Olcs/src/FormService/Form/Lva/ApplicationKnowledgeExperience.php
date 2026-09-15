<?php

declare(strict_types=1);

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\KnowledgeExperience;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Validator\ValidatorPluginManager;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

class ApplicationKnowledgeExperience extends KnowledgeExperience
{
    use ButtonsAlterations;

    public function __construct(
        protected FormHelperService $formHelper,
        protected AuthorizationService $authService,
        protected TranslationHelperService $translator,
        protected UrlHelperService $urlHelper,
        protected ValidatorPluginManager $validatorPluginManager
    ) {
    }

    #[\Override]
    protected function alterForm($form): void
    {
        parent::alterForm($form);

        $formActions = $form->get('form-actions');

        $saveButton = $formActions->get('save');
        $saveButton->setLabel('lva.external.save_and_return.link');
        $saveButton->removeAttribute('class');
        $saveButton->setAttribute(
            'class',
            'govuk-button govuk-button--secondary'
        );

        $formActions
            ->get('saveAndContinue')
            ->setLabel('lva.external.save_and_continue.button');

        $formActions->remove('cancel');
    }
}
