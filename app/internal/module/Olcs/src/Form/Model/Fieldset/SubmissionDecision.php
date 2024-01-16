<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmissionDecision-fields")
 */
class SubmissionDecision extends Base
{
    /**
     * @Form\Attributes({"id":"","placeholder":"", "class":"js-sub_st_rec", "multiple":false})
     * @Form\Options({
     *     "label": "Decision type",
     *     "category": "sub_st_dec",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     * })
     * @Form\Type("DynamicSelect")
     */
    public $actionTypes = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-medium js-sub-legislation",
     * "multiple" : true})
     * @Form\Options({
     *     "label": "Legislation",
     *     "service_name": "Olcs\Service\Data\SubmissionLegislation",
     *     "disable_inarray_validator": false,
     *     "use_groups":true
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $reasons = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long tinymce","name":"comment"})
     * @Form\Options({
     *     "label": "Decision reason",
     *     "label_attributes": {
     *         "class": ""
     *     },
     *     "column-size": "",
     * })
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Filter("htmlpurifier")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5})
     */
    public $comment = null;

    /**
     * @Form\Attributes({"value":"Y"})
     * @Form\Options({"value": "Y"})
     * @Form\Type("Hidden")
     */
    public $isDecision = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $submission = null;
}
