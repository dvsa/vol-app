<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 * @Form\Options({"label":"Add recommendation"})
 */
class RecommendMain
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Decision type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "submission_recommendation"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $actionTypes = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Select legislation",
     *     "disable_inarray_validator": false,
     *     "category": "pi-reasons"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $piReasons = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Send to",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "user-list"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $recipientUser = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Reason"})
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $comment = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Urgent"})
     * @Form\Type("OlcsCheckbox")
     */
    public $urgent = null;
}
