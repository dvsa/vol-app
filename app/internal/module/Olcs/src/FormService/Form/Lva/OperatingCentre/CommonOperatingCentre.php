<?php

/**
 * Common Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre as ComOperatinCentre;
use Zend\Form\Form;
use Zend\Validator\ValidatorChain;

/**
 * Common Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommonOperatingCentre extends ComOperatinCentre
{
    public function alterForm(Form $form, array $params)
    {
        $form->getInputFilter()->get('data')->remove('permission');
        $form->getInputFilter()->get('data')->remove('sufficientParking');
        parent::alterForm($form, $params);
    }
}
