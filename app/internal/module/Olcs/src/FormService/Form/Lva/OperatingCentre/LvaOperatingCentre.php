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
use Common\Validator\ValidateIf;

/**
 * Lva Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LvaOperatingCentre extends CommonOperatingCentre
{
    /**
     * Alter the Form
     *
     * @param Form  $form   Form to alter
     * @param array $params Data
     *
     * @return void
     */
    public function alterForm(Form $form, array $params)
    {
        $this->getFormHelper()->removeValidator($form, 'data->permission', ValidatorIdentical::class);
        $this->getFormHelper()->removeValidator($form, 'data->sufficientParking', ValidatorIdentical::class);
        // On Internal uploading the advert isn't mandatory
        $this->getFormHelper()->removeValidator($form, 'advertisements->uploadedFileCount', ValidateIf::class);
        parent::alterForm($form, $params);
    }
}
