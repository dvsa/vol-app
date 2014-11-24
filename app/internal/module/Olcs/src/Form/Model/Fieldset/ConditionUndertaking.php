<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("condition-undertaking")
 */
class ConditionUndertaking extends CaseBase
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $licence = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Condition / Undertaking Type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select",
     *     "category": "cond_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $conditionType = null;

    /**
     * @Form\Attributes({"value":"cav_case"})
     * @Form\Type("Hidden")
     */
    public $addedVia = null;

    /**
     * @Form\Attributes({"value":0})
     * @Form\Type("Hidden")
     */
    public $isDraft = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Description",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-6",
     *     "help-block": "You can type anything in this box."
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":"8000"}})
     */
    public $notes = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "label": "Fulfilled",
     *     "help-block": "Please choose"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $isFulfilled = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Attached to",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": true
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $attachedTo = null;
}
