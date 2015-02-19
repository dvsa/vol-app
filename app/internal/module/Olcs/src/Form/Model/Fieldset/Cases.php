<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

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
     *     "help-block": "Please select a case type",
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
     *     "help-block": "Please select a case category",
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
     *     },
     *     "help-block": "Description of the case"
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":1024}})
     */
    public $description = null;

    /**
     * @Form\Attributes({"class":"medium","placeholder":"ECMS Number","id":""})
     * @Form\Options({"label":"ECMS Number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":45}})
     */
    public $ecmsNo = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a case type",
     *     "category": "case_outcome"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $application = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $licence = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $transportManager = null;
}
