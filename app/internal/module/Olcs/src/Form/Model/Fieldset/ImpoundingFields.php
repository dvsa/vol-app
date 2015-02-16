<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("impounding_fields")
 */
class ImpoundingFields
{
    /**
     * @Form\Attributes({"id":"impoundingType","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Impounding type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "impound_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $impoundingType = null;

    /**
     * @Form\Attributes({"id":"applicationReceiptDate"})
     * @Form\Options({
     *     "label": "Application received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $applicationReceiptDate = null;

    /**
     * @Form\Attributes({"id":"vrm","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Vehicle registration mark",
     *     "label_attributes": {
     *         "class": "col-sm-2"
     *     },
     *     "column-size": "sm-5"
     * })
     * 
     * @Form\Type("Text")
     * @Form\Filter({"name":"Common\Filter\Vrm"})
     * @Form\Validator({"name":"Common\Form\Elements\Validators\Vrm"})
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"id":"impoundingLegislationTypes","placeholder":"","multiple":"multiple",
     *     "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Select legislation",
     *     "disable_inarray_validator": false,
     *     "help-block": "Use CTRL to select multiple",
     *     "service_name": "Olcs\Service\Data\ImpoundingLegislation",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $impoundingLegislationTypes = null;

    /**
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Hearing date",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "pattern": "d MMMM y '</div><div class=""field""><label for=hearingDate>Hearing time</label>'HH:mm:ss"
     * })
     * @Form\Required(false)
     * @Form\Type("DateTimeSelect")
     * @Form\AllowEmpty(true)
     * @Form\Filter({"name": "DateTimeSelectNullifier"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "impoundingType",
     *          "context_values": {"impt_hearing"},
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d H:i:s"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"applicationReceiptDate",
     *                      "compare_to_label":"Application received",
     *                      "operator": "gte",
     *                      "has_time": true
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $hearingDate = null;

    /**
     * @Form\Attributes({"id":"piVenue","placeholder":"","class":"medium", "required":false})
     * @Form\Options({
     *     "label": "Hearing location",
     *     "service_name": "Common\Service\Data\PiVenue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "other_option" : true
     * })
     *
     * @Form\AllowEmpty(true)
     * @Form\Type("DynamicSelect")
     */
    public $piVenue = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"medium","id":"piVenueOther", "required":false})
     * @Form\Options({"label":"Other hearing location"})
     * @Form\AllowEmpty(true)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "impoundingType",
     *          "context_values": {"impt_hearing"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name":"Zend\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $piVenueOther = null;

    /**
     * @Form\Attributes({"id":"presidingTc","placeholder":"","class":"medium"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Agreed by",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $presidingTc = null;

    /**
     * @Form\Attributes({"id":"outcome","placeholder":"","class":"medium"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "impound_outcome"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;

    /**
     * @Form\Attributes({"id":"outcomeSentDate"})
     * @Form\Options({
     *     "label": "Outcome sent date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $outcomeSentDate = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Notes/ECMS number"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $notes = null;
}
