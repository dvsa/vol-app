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

    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected TranslationHelperService $translator, protected UrlHelperService $urlHelper, protected ValidatorPluginManager $validatorPluginManager)
    {
    }

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form): void
    {
        parent::alterForm($form);
        $formActions = $form->get('form-actions');
        $saveButton = $formActions->get('save');
        $saveButton->setLabel('lva.external.save_and_return.link');
        $saveButton->removeAttribute('class');
        $saveButton->setAttribute('class', 'govuk-button govuk-button--secondary');
        $formActions->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $formActions->remove('cancel');
    }
}
