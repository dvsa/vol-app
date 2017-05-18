<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Addresses as CommonAddresses;

/**
 * Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Addresses extends CommonAddresses
{
    /**
     * Alter Form
     *
     * @param \Zend\Form\Form $form   Form
     * @param array           $params Params
     *
     * @return void
     */
    protected function alterForm(\Zend\Form\Form $form, array $params)
    {
        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        $this->removeEstablishment($form, $params['typeOfLicence']['licenceType']);

        //  change email settings
        /** @var \Zend\InputFilter\Input $emailElm */
        $emailElm = $form->getInputFilter()->get('contact')->get('email');
        $emailElm
            ->setRequired(false)
            ->setAllowEmpty(true);
    }
}
