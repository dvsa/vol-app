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
        $this->alterButtons($form);

        return $form;
    }
}
