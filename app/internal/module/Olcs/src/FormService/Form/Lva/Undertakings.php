<?php

/**
 * Undertakings Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Undertakings as CommonUndertakings;

/**
 * Undertakings Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Undertakings extends CommonUndertakings
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

        $this->getFormHelper()->remove($form, 'interim');

        return $form;
    }
}
