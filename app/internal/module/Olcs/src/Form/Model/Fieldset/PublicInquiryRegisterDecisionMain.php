<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 * @Form\Options({"label":""})
 */
class PublicInquiryRegisterDecisionMain
{
    /**
     * @Form\Attributes({"class":"","id":"", "readonly":true})
     * @Form\Options({"label":"PI number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $piNumber = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium", "readonly":true})
     * @Form\Options({
     *     "label": "Venue",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $venue = null;

    /**
     * @Form\Attributes({"class":"long","id":"", "readonly":true})
     * @Form\Options({"label":"Other venue"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $otherVenue = null;

    /**
     * @Form\Attributes({"id":"dob","class":"long", "readonly":true})
     * @Form\Options({
     *     "label": "Date of PI",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $piDate = null;

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
    public $presidingTc = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR role",
     *     "category": "tc_role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidingRole = null;

    /**
     * @Form\Attributes({"class":"medium","id":"", "readonly":true, "multiple" : true})
     * @Form\Required(false)
     * @Form\Options({
     *     "label":"Reason for PI",
     *     "service_name": "Olcs\Service\Data\PublicInquiryReason",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reasons = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium", "multiple" : true})
     * @Form\Options({
     *     "label": "Decisions",
     *     "service_name": "Olcs\Service\Data\PublicInquiryDecision",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
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
     * @Form\Filter({"name":"Digits"})
     * @Form\Validator({"name":"Digits"})
     */
    public $witnesses = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $decisionDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of notification",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $notificationDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"large", "multiple":true})
     * @Form\Options({
     *     "label": "Definition",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "service_name": "\Olcs\Service\Data\PublicInquiryDefinition",
     *     "use_groups": true
     * })
     * @Form\Type("DynamicSelect")
     */
    public $definition = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
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
    public $detailsToBePublished = null;

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
