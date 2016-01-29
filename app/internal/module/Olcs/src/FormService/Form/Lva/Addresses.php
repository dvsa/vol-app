<?php

/**
 * Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Addresses as CommonAddresses;

/**
 * Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Addresses extends CommonAddresses
{
    protected function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        $form->getInputFilter()->get('contact')->get('email')->setRequired(false);
        $form->getInputFilter()->get('contact')->get('email')->setAllowEmpty(true);

        return $form;
    }
}
