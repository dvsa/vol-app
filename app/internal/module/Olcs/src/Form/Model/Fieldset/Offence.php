<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("offence")
 * @Form\Options({"label":"Offence details:","class":"extra-long"})
 */
class Offence
{
    /**
     * @Form\Attributes({"id":"parentCategory","placeholder":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Act/si",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": true,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $parentCategory = null;

    /**
     * @Form\Attributes({"id":"category","placeholder":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Conviction description",
     *     "value_options": {
     *
     *     },
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": true,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $convictionCategory = null;

    /**
     * @Form\Attributes({"id":"categoryText","class":"extra-long"})
     * @Form\Options({
     *     "label": "Conviction description detail",
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
    public $categoryText = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Offence date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {"compare_to":"convictionDate", "compare_to_label":"Conviction date", "operator": "lte"}
     * })
     */
    public $offenceDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Conviction date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $convictionDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "SI",
     *     "empty_option": "Please Select",
     *     "value_options": {"Y": "Yes", "N": "No"},
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("Select")
     */
    public $msi = null;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Court/FPN"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":70}})
     */
    public $court = null;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Penalty"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":255}})
     */
    public $penalty = null;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Costs"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":255}})
     */
    public $costs = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Conviction notes",
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
    public $notes = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Taken into consideration",
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
    public $takenIntoConsideration = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Declared to TC/TR",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Has this conviction been declared to traffic commissioner?",
     *     "value_options": {"Y": "Yes", "N": "No"},
     * })
     * @Form\Type("Select")
     */
    public $isDeclared = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Dealt with"})
     * @Form\Type("checkbox")
     */
    public $isDealtWith = null;
}
