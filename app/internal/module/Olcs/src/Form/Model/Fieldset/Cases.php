<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Cases form.
 */
class Cases extends Base
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Case type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "case_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $caseType = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium", "multiple": "true"})
     * @Form\Options({
     *     "label": "Case category",
     *     "disable_inarray_validator": true,
     *     "category": "case_category",
     *     "use_groups": true
     * })
     * @Form\Type("DynamicSelect")
     */
    public $categorys = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"Case outline","class":"long"})
     * @Form\Options({
     *     "label": "Case outline",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":1024})
     */
    public $description = null;

    /**
     * @Form\Attributes({"class":"medium","placeholder":"ECMS Number","id":""})
     * @Form\Options({"label":"ECMS Number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":45})
     */
    public $ecmsNo = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":"", "class":"chosen-select-medium","multiple":"multiple"})
     * @Form\Options({
     *     "label": "Outcome",
     *     "disable_inarray_validator": false,
     *     "category": "case_outcome"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $outcomes = null;
}
