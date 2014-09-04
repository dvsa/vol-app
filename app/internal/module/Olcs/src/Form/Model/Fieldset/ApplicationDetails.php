<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("application_details")
 * @Form\Options({"label":"Application details"})
 */
class ApplicationDetails
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Impounding type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "impound_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $impoundingType = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Application received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateNotInFuture")
     */
    public $applicationReceiptDate = null;

    /**
     * @Form\Attributes({"id":"vrm","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Vehicle registration mark",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-5",
     *     "help-block": "Between 2 and 50 characters."
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Filter({"name":"Zend\Filter\StringToUpper"})
     * @Form\Filter({
     *     "name": "Zend\Filter\PregReplace",
     *     "options": {
     *         "pattern": "/\ /",
     *         "replacement": ""
     *     }
     * })
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":7}})
     * @Form\Validator({"name":"Zend\I18n\Validator\Alnum"})
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Select legislation",
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "category": "impounding_legislation",
     *     "use_groups": "true"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $impoundingLegislationTypes = null;
}
