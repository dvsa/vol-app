<?php

/**
 * Licence Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Addresses;

/**
 * Licence Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceAddresses extends Addresses
{
    protected function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormServiceLocator()->get('lva-licence')->alterForm($form);

        return $form;
    }
}
