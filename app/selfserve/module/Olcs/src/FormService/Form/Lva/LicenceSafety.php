<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;
use Zend\Form\Form;

/**
 * Licence safety
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceSafety extends CommonSafety
{
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
        $form->get('form-actions')->get('save')->setAttribute('class', 'action--primary large');
        $this->getFormHelper()->remove($form, 'form-actions->cancel');

        return $form;
    }
}
