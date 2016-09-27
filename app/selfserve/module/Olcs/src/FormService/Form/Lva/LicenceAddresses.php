<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Addresses as CommonAddress;

/**
 * Licence address
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceAddresses extends CommonAddress
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form   form
     * @param array           $params params
     *
     * @return \Zend\Form\Form
     */
    protected function alterForm(\Zend\Form\Form $form, array $params)
    {
        parent::alterForm($form, $params);
        $form->get('form-actions')->get('save')->setAttribute('class', 'action--primary large');

        return $form;
    }
}
