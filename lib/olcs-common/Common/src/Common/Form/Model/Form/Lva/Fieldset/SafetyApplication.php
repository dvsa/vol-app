<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-safety-application")
 */
class SafetyApplication
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label":
     * "application_vehicle-safety_safety.application.suitableMaintenance",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "label_attributes": {
     *         "class": "inline"
     *     }
     * })
     * @Form\Type("\Laminas\Form\Element\Radio")
     */
    public $isMaintenanceSuitable;

    /**
     * @Form\Attributes({
     *     "id":"","placeholder":"",
     *     "data-container-class":"confirm"
     * })
     * @Form\Options({
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "label": "application_vehicle-safety_safety.application.safetyConfirmation",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $safetyConfirmation;
}
