<?php

/**
 * Lva Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre;
use Zend\Form\Form;

/**
 * Lva Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LvaOperatingCentre extends CommonOperatingCentre
{
    public function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);
    }
}
