<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("details")
 * @Form\Options({"label":"Appeal Details"})
 */
class Details
{
    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Appeal deadline",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateRequired")
     */
    public $deadlineDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of appeal",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\DateRequired")
     */
    public $appealDate = null;

    /**
     * @Form\Attributes({"class":"","id":""})
     * @Form\Options({"label":"Appeal number"})
     * @Form\Required(false)
     * @Form\AllowEmpty(true)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":2,"max":20}})
     */
    public $appealNo = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Reason",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "appeal_reason"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $reason = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Outline ground"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $outlineGround = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of appeal hearing",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $hearingDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $decisionDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Papers due at tribunal",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $papersDueDate = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Papers sent date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Custom\DateSelect")
     */
    public $papersSentDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category",
     *     "category": "appeal_outcome"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $outcome = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Comments"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":5,"max":4000}})
     */
    public $comment = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Is
     * withdrawn?"})
     * @Form\Type("checkbox")
     */
    public $isWithdrawn = null;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Withdrawn date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("\Common\Form\Elements\InputFilters\WithdrawnDate")
     */
    public $withdrawnDate = null;
}
