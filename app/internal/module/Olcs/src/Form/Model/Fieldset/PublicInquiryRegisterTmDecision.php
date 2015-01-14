<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 * @Form\Options({"label":""})
 */
class PublicInquiryRegisterTmDecision extends CaseBase
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $decidedByTc = null;

    /**
     * @Form\Attributes({"id":"decisionDate"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $decisionDate = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"decisions","class":"chosen-select-large",
     *     "required": false})
     * @Form\Options({
     *     "label": "Decisions",
     *     "service_name": "Olcs\Service\Data\PublicInquiryDecision",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "use_groups":true
     * })
     * @Form\Type("DynamicSelect")
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "decisionDate",
     *          "contextValues": {null},
     *          "context_truth": false,
     *          "validators": {
     *              {"name": "NotEmpty"}
     *          }
     *      }
     * })
     */
    public $decisions = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Witnesses"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Validator({"name":"Digits"})
     */
    public $witnesses = null;

    /**
     * @Form\Attributes({"id":"notificationDate"})
     * @Form\Options({
     *     "label": "Date of notification",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $notificationDate = null;

    /**
     * @Form\Attributes({"id":"definition","placeholder":"","class":"chosen-select-large js-definition-source",
     *     "multiple":true})
     * @Form\Options({
     *     "label": "Definition",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "service_name": "\Olcs\Service\Data\PublicInquiryDefinition",
     *     "use_groups": true
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $definition = null;

    /**
     * @Form\Attributes({"id":"decisionNotes","class":"extra-long    js-definition-target"})
     * @Form\Options({
     *     "label": "Details to be published",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-6",
     *     "help-block": "You can type anything in this box."
     * })
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $decisionNotes = null;
}
