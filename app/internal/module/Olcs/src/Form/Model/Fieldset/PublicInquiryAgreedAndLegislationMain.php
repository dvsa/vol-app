<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 * @Form\Options({"label":""})
 */
class PublicInquiryAgreedAndLegislationMain
{
    /**
     * @Form\Options({
     *     "label": "Agreed date",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $agreedDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Agreed by",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $agreedByTc = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Agreed by role",
     *     "category": "tc_role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $agreedByTcRole = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"", "class":"chosen-select-medium", "multiple":true})
     * @Form\Options({
     *     "label": "Type of PI",
     *     "category": "pi_type",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name":"Olcs\Validator\TypeOfPI"})
     */
    public $piTypes = null;

    /**
     * @Form\Attributes({
     *      "id":"","placeholder":"",
     *      "class":"chosen-select-medium",
     *      "multiple" : true
     * })
     * @Form\Options({
     *     "label": "Legislation",
     *     "service_name": "Olcs\Service\Data\PublicInquiryReason",
     *     "disable_inarray_validator": false,
     *     "use_groups":true
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reasons = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Comments"
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $comment = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $case = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;
}
