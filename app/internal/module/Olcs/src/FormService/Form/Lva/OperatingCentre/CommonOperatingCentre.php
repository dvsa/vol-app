<?php

/**
 * Common Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre as ComOperatingCentre;
use Zend\Form\Form;
use Zend\Validator\ValidatorChain;
use Zend\Validator\Identical as ValidatorIdentical;

/**
 * Common Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommonOperatingCentre extends ComOperatingCentre
{
    public function alterForm(Form $form, array $params)
    {
        $this->getFormHelper()->removeValidator($form, 'data->permission', ValidatorIdentical::class);
        $this->getFormHelper()->removeValidator($form, 'data->sufficientParking', ValidatorIdentical::class);
        parent::alterForm($form, $params);
    }
}
