<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("condition-undertaking")
 */
class ConditionUndertaking extends CaseBase
{
    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Condition / Undertaking type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "cond_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $type = null;

    /**
     * @Form\Attributes({"value":0})
     * @Form\Type("Hidden")
     */
    public $isDraft = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Description"
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
     *     "label": "Fulfilled"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $fulfilled = null;

    /**
     * value options are set in the controller under alter form methods
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
