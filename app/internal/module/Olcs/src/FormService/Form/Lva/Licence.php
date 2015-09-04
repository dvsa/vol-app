<?php

/**
 * Licence Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Licence as CommonLicence;

/**
 * Licence Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Licence extends CommonLicence
{
    public function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');
    }
}
