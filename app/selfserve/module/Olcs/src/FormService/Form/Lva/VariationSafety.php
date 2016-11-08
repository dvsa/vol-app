<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;

/**
 * Variation safety
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationSafety extends CommonSafety
{
    /**
     * Returns form
     *
     * @return \Zend\Form\FormInterface
     */
    public function getForm()
    {
        $form = parent::getForm();

        $this->getFormHelper()->remove($form, 'form-actions->cancel');

        return $form;
    }
}
