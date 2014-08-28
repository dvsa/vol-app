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
     * Whether or not the fields are required
     *
     * @var boolean
     */
    protected $requiredFields = false;

    /**
     * Remove the trailer fields for PSV
     *
     * @param \Zend\Form\Fieldset $form
     * @return \Zend\Form\Fieldset
     */
    protected function alterForm($form)
    {
        $form = parent::alterForm($form);

        if (!$this->requiredFields) {
            $this->setFieldsAsNotRequired($form->getInputFilter());
        }

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

    /**
     * Update the section statuses
     *  - Internally we remove the required field validation, so here we need to explicitly check if the section is
     *      complete
     *
     * @param array $subSections
     */
    protected function updateSectionStatuses(array $subSections)
    {
        $completion = parent::updateSectionStatuses($subSections);
        $sectionIndex = $this->formatSectionStatusIndex();

        // Get a new form without the removed validation
        $this->requiredFields = true;
        $form = $this->getNewForm();
        $form->remove('csrf');

        $formData = $form->getData();

        $flatFormData = $this->getFlatData($formData);

        $started = $this->hasSectionBeenStarted($flatFormData);

        $incompleteStatus = self::COMPLETION_STATUS_NOT_STARTED;

        if ($started) {
            $incompleteStatus = self::COMPLETION_STATUS_INCOMPLETE;
        }

        // Check if the form would be valid
        $completion[$sectionIndex] = $form->isValid() ? self::COMPLETION_STATUS_COMPLETE : $incompleteStatus;

        return $completion;
    }

    /**
     * Check if the section has been started
     *
     * @param array $data
     * @return boolean
     */
    protected function hasSectionBeenStarted($data)
    {
        // Loop through the data
        foreach ($data as $key => $value) {
            // Check if we have any non-default values
            switch ($key) {
                case 'rows':
                    if ($value > 0) {
                        return true;
                    }
                    break;
                case 'safetyConfirmation':
                    if ($value != 'N') {
                        return true;
                    }
                    break;
                default:
                    if (!empty($value)) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Flattern the data
     *
     * @param array $data
     * @return array
     */
    protected function getFlatData($data)
    {
        $flatData = array_merge($data['licence'], $data['table'], $data['application']);
        unset($flatData['id']);
        unset($flatData['version']);
        unset($flatData['table']);
        unset($flatData['action']);

        return $flatData;
    }
}
