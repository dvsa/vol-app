<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class PublicInquiryHearingFields extends Base
{
    /**
     * @Form\Attributes({"id":"piVenue","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Venue",
     *     "service_name": "Olcs\Service\Data\PiVenue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "other_option" : true
     * })
     *
     * @Form\Type("DynamicSelect")
     */
    public $piVenue;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"piVenueOther", "required":false})
     * @Form\Options({"label":"Other venue"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "piVenue",
     *          "context_values": {"other"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *              {"name":"Zend\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })

     */
    public $piVenueOther;

    /**
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Date of PI",
     *     "create_empty_option": true,
     *     "max_year": 2016,
     *     "render_delimiters": true,
     *     "pattern": "d MMMM y '</div><div class=""field""><label for=hearingDate>Time of PI</label>'HH:mm:ss",
     *     "category": "pi_hearing",
     *     "field": "hearingDate"
     * })
     * @Form\Type("SlaDateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d H:i:s"}})
     */
    public $hearingDate;

    /**
     * @Form\Attributes({"id":"presidingTc","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidingTc;

    /**
     * @Form\Attributes({"id":"presidedByRole","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/TR/DTR Role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "tc_role"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidedByRole;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Witnesses"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Validator({"name":"Digits"})
     */
    public $witnesses;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Cancelled"})
     * @Form\Type("checkbox")
     */
    public $isCancelled;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"cancelledDate", "required":false})
     * @Form\Options({
     *     "label": "Cancelled date",
     *     "create_empty_option": true,
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isCancelled",
     *          "context_values": {"Y"},
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}}
     *          }
     *      }
     * })
     */
    public $cancelledDate;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"extra-long","id":"cancelledReason", "required":false})
     * @Form\Options({"label":"Cancelled reason"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isCancelled",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"Zend\Validator\NotEmpty"},
     *              {"name":"Zend\Validator\StringLength","options":{"max":1000}}
     *          }
     *      }
     * })
     */
    public $cancelledReason;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Adjourned"})
     * @Form\Type("checkbox")
     */
    public $isAdjourned;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"adjournedDate", "required": false})
     * @Form\Options({
     *     "label": "Adjourned date",
     *     "create_empty_option": true,
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isAdjourned",
     *          "context_values": {"Y"},
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}}
     *          }
     *      }
     * })
     */
    public $adjournedDate;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"extra-long","id":"", "required":false})
     * @Form\Options({"label":"Adjourned reason"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isAdjourned",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"Zend\Validator\NotEmpty"},
     *              {"name":"Zend\Validator\StringLength","options":{"max":1000}}
     *          }
     *      }
     * })
     */
    public $adjournedReason = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":"","class":"extra-long tall  js-definition-source", "multiple":true})
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
     * @Form\Attributes({"class":"extra-long    js-definition-target","id":""})
     * @Form\Options({"label":"Details to be published"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5, "max":4000}})
     */
    public $details = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $pi;
}
