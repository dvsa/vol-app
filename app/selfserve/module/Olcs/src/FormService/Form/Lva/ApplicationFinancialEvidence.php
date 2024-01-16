<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialEvidence;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Laminas\Form\Form;
use Laminas\Validator\ValidatorPluginManager;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application financial evidence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationFinancialEvidence extends FinancialEvidence
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected UrlHelperService $urlHelper;
    protected TranslationHelperService $translator;
    protected ValidatorPluginManager $validatorPluginManager;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        TranslationHelperService $translator,
        UrlHelperService $urlHelper,
        $validatorPluginManager
    ) {
        $this->formHelper = $formHelper;
        $this->authService = $authService;
        $this->urlHelper = $urlHelper;
        $this->translator = $translator;
        $this->validatorPluginManager = $validatorPluginManager;
    }

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $formActions = $form->get('form-actions');
        $saveButton = $formActions->get('save');
        $saveButton->setLabel('lva.external.save_and_return.link');
        $saveButton->removeAttribute('class');
        $saveButton->setAttribute('class', 'govuk-button govuk-button--secondary');
        $formActions->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $formActions->remove('cancel');

        return $form;
    }
}
