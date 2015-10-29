<?php

/**
 * Lva Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre;
use Zend\Form\Form;
use Zend\Validator\Identical as ValidatorIdentical;

/**
 * Lva Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LvaOperatingCentre extends CommonOperatingCentre
{
    public function alterForm(Form $form, array $params)
    {
        $this->getFormHelper()->removeValidator($form, 'data->permission', ValidatorIdentical::class);
        $this->getFormHelper()->removeValidator($form, 'data->sufficientParking', ValidatorIdentical::class);
        parent::alterForm($form, $params);
    }
}
