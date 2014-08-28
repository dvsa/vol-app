<?php

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Journey\Application\VehicleSafety;

use Common\Controller\Application\VehicleSafety\SafetyController as ParentController;
use Zend\InputFilter\InputFilter;

/**
 * Safety Controller
 *
 * Here we extend the Application Journey Safety controller for use in internal
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends ParentController
{
    /**
     * Whether or not to hide internal form elements
     *
     * @var boolean
     */
    protected $hideInternalFormElements = true;

    /**
     * Remove the trailer fields for PSV
     *
     * @param \Zend\Form\Fieldset $form
     * @return \Zend\Form\Fieldset
     */
    protected function alterForm($form)
    {
        $form = parent::alterForm($form);

        $this->setFieldsAsNotRequired($form->getInputFilter());

        return $form;
    }

    /**
     * Set fields as not required
     *
     * @param \Zend\InputFilter\InputFilter $inputFilter
     */
    protected function setFieldsAsNotRequired($inputFilter)
    {
        $inputs = $inputFilter->getInputs();

        foreach ($inputs as $input) {
            if ($input instanceof InputFilter) {
                $input = $this->setFieldsAsNotRequired($input);
            } else {
                $input->setRequired(false);
                $input->setAllowEmpty(true);
            }
        }

        return $inputs;
    }
}
