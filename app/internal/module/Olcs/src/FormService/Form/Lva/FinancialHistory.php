<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialHistory as CommonFinancialHistory;
use Common\Form\Form;

/**
 * FinancialHistory Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialHistory extends CommonFinancialHistory
{
    /**
     * Make form alterations
     *
     * @param Form  $form Form
     * @param array $data Parameters for form
     *
     * @return \Zend\Form\Form
     */
    protected function alterForm(Form $form, array $data = [])
    {
        parent::alterForm($form, $data);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
