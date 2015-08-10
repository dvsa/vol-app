<?php

/**
 * PsvVehicles Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\PsvVehicles as CommonPsvVehicles;

/**
 * PsvVehicles Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class PsvVehicles extends CommonPsvVehicles
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
