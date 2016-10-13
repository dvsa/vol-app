<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;
use Zend\Form\Form;

/**
 * Variation safety
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationSafety extends CommonSafety
{
    /**
     * Alter form
     *
     * @param Form $form form
     *
     * @return void
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->getFormHelper()->remove($form, 'form-actions->cancel');
    }
}
