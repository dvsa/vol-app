<?php

/**
 * FinancialHistory Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\FinancialHistory as CommonFinancialHistory;

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
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
