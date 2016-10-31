<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialEvidence;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application financial evidence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationFinancialEvidence extends FinancialEvidence
{
    use ButtonsAlterations;

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
        $saveButton->setAttribute('class', 'action--tertiary large');
        $formActions->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $formActions->remove('cancel');

        return $form;
    }
}
