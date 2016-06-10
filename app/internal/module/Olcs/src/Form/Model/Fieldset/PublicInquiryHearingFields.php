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
     * @Form\Attributes({"id":"venue","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Venue",
     *     "service_name": "Common\Service\Data\Venue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "other_option" : true
     * })
     *
     * @Form\Type("DynamicSelect")
     */
    public $venue;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"medium","id":"venueOther", "required":false})
     * @Form\Options({"label":"Other venue"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("Text")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "venue",
     *          "context_values": {"other"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Zend\Validator\NotEmpty"},
     *              {"name":"Zend\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $venueOther;

    /**
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Date of PI",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "pattern": "d MMMM y '{{SLA_HINT}}</fieldset><fieldset><div class=""field""><label for=""hearingDate"">Time of PI</label>'HH:mm:ss'</div>'",
     *     "category": "pi_hearing",
     *     "field": "hearingDate"
     * })
     * @Form\Type("DateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d H:i:s"}})
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
     * @Form\Attributes({"id":"presidingTc","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/HTRU/DHTRU",
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
     *     "label": "Presiding TC/DTC/HTRU/DHTRU role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "tc_role"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidedByRole;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({"label": "Number of witnesses"})
     * @Form\Type("Text")
     * @Form\Validator({"name":"Digits"})
     */
    public $witnesses;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Cancelled / Withdrawn"})
     * @Form\Type("OlcsCheckbox")
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
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"Zend\Validator\NotEmpty"},
     *              {"name": "\Common\Validator\Date"},
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
     * @Form\Type("OlcsCheckbox")
     */
    public $isAdjourned;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"adjournedDate"})
     * @Form\Options({
     *     "label": "Date adjournment agreed",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "pattern": "d MMMM y '</fieldset><fieldset><div class=""field""><label for=""adjournedDate"">Time adjournment agreed</label>'HH:mm:ss'</div>'",
     *     "field": "adjournedDate"
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateTimeSelect")
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isAdjourned",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d H:i:s"}},
     *              {"name": "\Zend\Validator\NotEmpty"}
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
     * @Form\Attributes({"id":"","placeholder":"","class":"chosen-select-large js-definition-source"})
     * @Form\Options({
     *     "label": "Definition",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "service_name": "\Olcs\Service\Data\PublicInquiryDefinition",
     *     "use_groups": true,
     *     "empty_option": "Add definition option"
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
     * @Form\Type("Select")
     * @Form\Options({
     *      "label": "Publication type",
     *      "value_options":{
     *          "All":"All",
     *          "A&D":"A&D",
     *          "N&P":"N&P"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"pubType",
     *      "value":"All"
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
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $pi;
}
