<?php

/**
 * Licence Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Zend\Form\Form;
use Zend\Validator\ValidatorChain;

/**
 * Licence Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOperatingCentre extends CommonOperatingCentre
{
    public function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);
    }
}
