<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 * @Form\Options({"label":""})
 */
class PublicInquiryRegisterTmDecision extends CaseBase
{
    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/HTRU/DHTRU",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     */
    public $decidedByTc = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Presiding TC/DTC/HTRU/DHTRU role",
     *     "category": "tc_role",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     */
    public $decidedByTcRole = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"decisionDate"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": true
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $decisionDate = null;

    /**
     * @Form\Required(true)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"decisions","class":"chosen-select-large",
     *     "required": false, "multiple":true})
     * @Form\Options({
     *     "label": "Decisions",
     *     "service_name": "Olcs\Service\Data\PublicInquiryDecision",
     *     "disable_inarray_validator": false,
     *     "use_groups":true
     * })
     */
    public $decisions = null;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "Number of witnesses"})
     * @Form\Validator("Digits")
     */
    public $witnesses = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":"notificationDate"})
     * @Form\Options({
     *     "label": "Date of notification",
     *     "create_empty_option": true,
     *     "render_delimiters": true
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $notificationDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"definition","placeholder":"","class":"chosen-select-large js-definition-source"})
     * @Form\Options({
     *     "label": "Definition",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\PublicInquiryDefinition",
     *     "use_groups": true,
     *     "empty_option": "Add definition option"
     * })
     */
    public $definition = null;

    /**
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Attributes({"id":"decisionNotes","class":"extra-long js-definition-target"})
     * @Form\Options({
     *     "label": "Details to be published"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $decisionNotes = null;

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
     *      "multiple":true,
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
}
