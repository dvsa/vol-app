<?php

/**
 * Financial Evidence Alterations
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\FormService\Form\Lva\Traits;

/**
 * Financial Evidence Alterations
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait FinancialEvidenceAlterations
{
    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
