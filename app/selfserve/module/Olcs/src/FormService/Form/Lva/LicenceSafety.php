<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;

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
     * @param \Zend\Form\Form $form form
     *
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $form->get('form-actions')->get('save')->setAttribute('class', 'action--primary large');

        return $form;
    }
}
