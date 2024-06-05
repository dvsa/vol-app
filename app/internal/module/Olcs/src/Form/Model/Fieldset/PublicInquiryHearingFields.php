<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class PublicInquiryHearingFields extends Base
{
    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"venue","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Venue",
     *     "service_name": "Common\Service\Data\Venue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "other_option" : true
     * })
     */
    public $venue;

    /**
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium","id":"venueOther", "required":false})
     * @Form\Options({"label":"Other venue"})
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "venue",
     *          "context_values": {"other"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "NotEmpty"},
     *              {"name":"Laminas\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $venueOther;

    /**
     * @Form\Required(true)
     * @Form\Type("DateTimeSelect")
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Date of PI",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "category": "pi_hearing",
     *     "field": "hearingDate"
     * })
     * @Form\Filter({"name":"DateTimeSelect", "options":{"null_on_empty":true}})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d H:i:s"})
     */
    public $hearingDate;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Hearing length",
     *      "value_options":{
     *          "not-set":"Not set",
     *          "N":"Half day",
     *          "Y":"Full day"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "not-set"})
     */
    public $isFullDay;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"presidingTc","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/HTRU/DHTRU",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     * })
     */
    public $presidingTc;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"presidedByRole","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/HTRU/DHTRU role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "tc_role"
     * })
     */
    public $presidedByRole;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({"label": "Number of witnesses"})
     * @Form\Validator("Digits")
     */
    public $witnesses;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({"label": "Number of drivers","error-message" : "digits.validation.zero-to-ninety-nine"})
     * @Form\Validator({"name":"Digits",
     *                  "options": {
     *                      "messages": {
     *                          "notDigits" : "digits.validation.zero-to-ninety-nine",
     *                          "digitsStringEmpty" : "digits.validation.zero-to-ninety-nine",
     *                          "digitsInvalid" : "digits.validation.zero-to-ninety-nine"
     *                      },
     *                      "break_chain_on_failure": true,
     *                  }
     *              })
     * @Form\Validator({"name":"NotEmpty",
     *                  "options": {
     *                      "messages": {
     *                          "isEmpty" : "digits.validation.zero-to-ninety-nine"
     *                      },
     *                      "break_chain_on_failure": true,
     *                  }
     *              })
     * @Form\Validator({"name":"Laminas\Validator\Between",
     *                  "options":{
     *                      "min":0,
     *                      "max":99,
     *                      "inclusive":true,
     *                      "messages": {
     *                          "notBetween" : "digits.validation.zero-to-ninety-nine"
     *                          }
     *                  }
     *              })
     */
    public $drivers;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Cancelled / Withdrawn"})
     */
    public $isCancelled;

    /**
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"cancelledDate", "required":false})
     * @Form\Options({
     *     "label": "Cancelled date",
     *     "create_empty_option": true,
     * })
     * @Form\Filter({"name":"DateSelect", "options":{"null_on_empty":true}})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isCancelled",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"NotEmpty"},
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}}
     *          }
     *      }
     * })
     */
    public $cancelledDate;

    /**
     * @Form\Required(true)
     * @Form\Type("TextArea")
     * @Form\Attributes({"class":"extra-long","id":"cancelledReason", "required":false})
     * @Form\Options({"label":"Cancelled reason"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isCancelled",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"NotEmpty"},
     *              {"name":"Laminas\Validator\StringLength","options":{"max":1000}}
     *          }
     *      }
     * })
     */
    public $cancelledReason;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Adjourned"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isAdjourned;

    /**
     * @Form\Required(true)
     * @Form\Type("DateTimeSelect")
     * @Form\Attributes({"id":"adjournedDate"})
     * @Form\Options({
     *     "label": "Date adjournment agreed",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "field": "adjournedDate"
     * })
     * @Form\Filter({"name":"DateTimeSelect", "options":{"null_on_empty":true}})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isAdjourned",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"NotEmpty"},
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d H:i:s"}}
     *          }
     *      }
     * })
     */
    public $adjournedDate;

    /**
     * @Form\Required(true)
     * @Form\Type("TextArea")
     * @Form\Attributes({"class":"extra-long","id":"", "required":false})
     * @Form\Options({"label":"Adjourned reason"})
     * @Form\Validator("NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isAdjourned",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"NotEmpty"},
     *              {"name":"Laminas\Validator\StringLength","options":{"max":1000}}
     *          }
     *      }
     * })
     */
    public $adjournedReason = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-large js-definition-source"})
     * @Form\Options({
     *     "label": "Definition",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\PublicInquiryDefinition",
     *     "use_groups": true,
     *     "empty_option": "Add definition option"
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $definition = null;

    /**
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Attributes({"class":"extra-long    js-definition-target","id":""})
     * @Form\Options({"label":"Details to be published"})
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5, "max":4000})
     */
    public $details = null;

    /**
     * @Form\Type("Select")
     * @Form\Attributes({
     *      "id":"pubType",
     *      "value":"All"
     * })
     * @Form\Options({
     *      "label": "Publication type",
     *      "value_options":{
     *          "All":"All",
     *          "A&D":"A&D",
     *          "N&P":"N&P"
     *      }
     * })
     */
    public $pubType;

    /**
     * @Form\Type("Select")
     * @Form\Attributes({
     *      "id":"trafficAreas",
     *      "placeholder":"",
     *      "multiple":"multiple",
     *      "value":"all",
     *      "class":"chosen-select-large"
     * })
     * @Form\Options({
     *      "label": "Traffic areas",
     *      "value_options":{
     *          "all":"All traffic areas",
     *          "B":"North East of England",
     *          "C":"North West of England",
     *          "D":"West Midlands",
     *          "F":"East of England",
     *          "G":"Wales",
     *          "H":"West of England",
     *          "K":"London and the South East of England",
     *          "M":"Scotland",
     *          "N":"Northern Ireland"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"trafficAreas"
     * })
     */
    public $trafficAreas;

    /**
     * @Form\Type("Hidden")
     * @Form\Attributes({"value":""})
     */
    public $pi;
}
