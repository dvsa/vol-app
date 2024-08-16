<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("offence")
 * @Form\Options({"label":"Offence details:","class":"extra-long"})
 */
class Offence extends Base
{
    /**
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"defendantType","placeholder":""})
     * @Form\Options({
     *     "label": "Defendant type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "def_type"
     * })
     */
    public $defendantType = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"placeholder":"First name","required":false})
     * @Form\Options({"label":"First name"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf", options={
     *     "context_field": "defendantType",
     *     "context_values": {"def_t_op", ""},
     *     "context_truth": false,
     *     "validators": {
     *          {"name":"Laminas\Validator\StringLength","options":{"min":2,"max":35}}
     *     }}
     * )
     */
    public $personFirstname = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"placeholder":"Last name","required":false})
     * @Form\Options({"label":"Last name"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf", options={
     *     "context_field": "defendantType",
     *     "context_values": {"def_t_op", ""},
     *     "context_truth": false,
     *     "validators": {
     *          {"name":"Laminas\Validator\StringLength","options":{"min":2,"max":35}}
     *     }}
     * )
     */
    public $personLastname = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Options({
     *     "label": "Date of birth",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Attributes({"required":false})
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("ValidateIf", options={
     *     "context_field": "defendantType",
     *     "context_values": {"def_t_op", ""},
     *     "context_truth": false,
     *     "validators": {
     *          {"name": "\Common\Validator\Date"},
     *          {"name": "Date", "options":{"format":"Y-m-d"}},
     *          {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *     }}
     * )
     */
    public $birthDate = null;

    /**
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Attributes({"id":"category","placeholder":"","class":"long chosen-select-medium"})
     * @Form\Options({
     *     "label": "Conviction description",
     *     "empty_option": "User Defined",
     *     "disable_inarray_validator": false,
     *     "category": "conv_category",
     *     "use_groups": true
     * })
     * @Form\Filter("Common\Filter\NullToArray")
     * @Form\Validator("NotEmpty", options={"array"})
     */
    public $convictionCategory = null;

    /**
     * @Form\Required(true)
     * @Form\Type("TextArea")
     * @Form\Options({
     *     "label": "Conviction description detail",
     * })
     * @Form\Attributes({
     *   "id":"categoryText",
     *   "class":"extra-long",
     *   "required":false
     * })
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("ValidateIf", options={
     *     "context_field": "convictionCategory",
     *     "context_values": {""},
     *     "context_truth": true,
     *     "validators": {
     *          {"name":"Laminas\Validator\StringLength","options":{"min":5,"max":4000}}
     *     }}
     * )
     */
    public $categoryText = null;

    /**
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Offence date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("Laminas\Filter\DateSelect", options={"null_on_empty": true})
     * @Form\Validator("NotEmpty", options={"array"})
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     * @Form\Validator("DateCompare",
     *      options={"compare_to":"convictionDate", "compare_to_label":"Conviction date", "operator": "lte"}
     * )
     */
    public $offenceDate = null;

    /**
     * @Form\Type("DateSelect")
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Conviction date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("\Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $convictionDate = null;

    /**
     * @Form\Type("Select")
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "SI",
     *     "empty_option": "Please Select",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "disable_inarray_validator": false
     * })
     */
    public $msi = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Court/FPN"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":70})
     */
    public $court = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Penalty"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":255})
     */
    public $penalty = null;

    /**
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Costs"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":255})
     */
    public $costs = null;

    /**
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Conviction notes"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $notes = null;

    /**
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Taken into consideration"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $takenIntoConsideration = null;

    /**
     * @Form\Type("Select")
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Declared to TC/TR",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "value_options": {"Y": "Yes", "N": "No"},
     * })
     */
    public $isDeclared = null;

    /**
     * @Form\Type("OlcsCheckbox")
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Dealt with"})
     */
    public $isDealtWith = null;
}
