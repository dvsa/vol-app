<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety as CommonSafety;

/**
 * Safety Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Safety extends CommonSafety
{
    /**
     * Make form alterations
     *
     * @return \Zend\Form\Form
     */
    public function getForm()
    {
        $form = parent::getForm();

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
