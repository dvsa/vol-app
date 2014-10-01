<?php

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Journey\Application\VehicleSafety;

use Common\Controller\Application\VehicleSafety\SafetyController as ParentController;
use Zend\Validator\ValidatorChain;

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
    protected $hideInternalFormElements = false;

    /**
     * Whether the fields are required or not
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

            $filter = $form->getInputFilter();

            $this->setFieldsAsNotRequired($filter);

            // Need to remove the validation for required confirm box
            $filter->get('application')->get('safetyConfirmation')->setValidatorChain(new ValidatorChain());
        }

        return $form;
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
        $this->setPersist(false);
        $form = $this->getNewForm();
        $form->remove('csrf');

        $valid = $form->isValid();
        $formData = $form->getData();

        $flatFormData = $this->getFlatData($formData);

        $started = $this->hasSectionBeenStarted($flatFormData);

        $incompleteStatus = self::COMPLETION_STATUS_NOT_STARTED;

        if ($started) {
            $incompleteStatus = self::COMPLETION_STATUS_INCOMPLETE;
        }

        // Check if the form would be valid
        $completion[$sectionIndex] = $valid ? self::COMPLETION_STATUS_COMPLETE : $incompleteStatus;

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
        foreach ($data as $key => $value) {

            // If we have rows in the table
            if ($key === 'rows' && $value > 0) {
                return true;
            }

            // If we have checked the safetyConfirmation
            if ($key === 'safetyConfirmation' && $value != 'N') {
                return true;
            }

            // If we have any other entered values
            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Flatten the data
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
