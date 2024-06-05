<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $applicationReceiptDate = null;

    /**
     * @Form\Attributes({"id":"vrm","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Vehicle registration mark"
     * })
     *
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1,"max":20})
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"id":"impoundingLegislationTypes","placeholder":"","multiple":"multiple",
     *     "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Grounds of application for return",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\ImpoundingLegislation",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $impoundingLegislationTypes = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateTimeSelect")
     * @Form\Attributes({"id":"hearingDate"})
     * @Form\Options({
     *     "label": "Hearing date",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     * })
     * @Form\Filter("Laminas\Filter\DateTimeSelect", options={"null_on_empty":true})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "impoundingType",
     *          "context_values": {"impt_hearing"},
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
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
     * )
     */
    public $hearingDate = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     * @Form\Attributes({"id":"venue","placeholder":"","class":"medium", "required":false})
     * @Form\Options({
     *     "label": "Hearing location",
     *     "service_name": "Common\Service\Data\Venue",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "other_option" : true
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $venue = null;

    /**
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Options({"label":"Other hearing location"})
     * @Form\Attributes({"class":"medium","id":"venueOther"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf",
     *      options={
     *          "context_field": "impoundingType",
     *          "context_values": {"impt_hearing"},
     *          "validators": {
     *              {
     *                  "name":"ValidateIf",
     *                  "options":
     *                      {
     *                          "context_field": "venue",
     *                          "context_values": {"other"},
     *                          "validators": {
     *                              {"name":"Laminas\Validator\StringLength","options":{"max":255}},
     *                              {"name":"Laminas\Validator\NotEmpty"}
     *                          }
     *                      }
     *              }
     *          }
     *      }
     * )
     */
    public $venueOther = null;

    /**
     * @Form\Attributes({"id":"presidingTc","placeholder":"","class":"medium"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Agreed by",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $presidingTc = null;

    /**
     * @Form\Attributes({"id":"outcome","placeholder":"","class":"medium"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "impound_outcome"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter("Common\Filter\NullToArray")
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
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $outcomeSentDate = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Notes/ECMS number"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $notes = null;
}
