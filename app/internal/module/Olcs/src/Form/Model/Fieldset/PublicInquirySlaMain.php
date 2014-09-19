<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 * @Form\Options({"label":""})
 */
class PublicInquirySlaMain
{
    //MLH

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Agreed date",
     *     "create_empty_option": false,
     *     "render_delimiters": "d m y",
     *     "day_attributes": {"disabled":true},
     *     "month_attributes": {"disabled":true},
     *     "year_attributes": {"disabled":true}
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $agreedDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"", "class":"long tall", "multiple":true, "disabled":true})
     * @Form\Options({
     *     "label": "Type of PI",
     *     "category": "pi_type",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     * @Form\Validator({"name":"Olcs\Validator\TypeOfPI"})
     */
    public $piTypes = null;

    //date of PI

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date of decision",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y",
     *     "day_attributes": {"disabled":true},
     *     "month_attributes": {"disabled":true},
     *     "year_attributes": {"disabled":true}
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $decisionDate = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long", "disabled":true})
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
    public $decisionNotes = null;

    /**
     * @Form\Options({
     *     "label": "Call up letter issued",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $callUpLetterIssued = null;

    /**
     * @Form\Options({
     *     "label": "Brief to TC",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $briefToTc = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Written outcome",
     *     "category": "written_outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a category"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $writtenOutcome = null;

    /**
     * @Form\Options({
     *     "label": "Date of written reason",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"blagle"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $dateOfWrittenReason = null;

    /**
     * @Form\Options({
     *     "label": "Decision letter sent",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"blagle"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $decisionLetterSend = null;

    /**
     * @Form\Options({
     *     "label": "Date of TC's written decision",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"blagle"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $dateOfTcWrittenDecision = null;

    /**
     * @Form\Options({
     *     "label": "Date of TC's written reason",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"blagle"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $dateOfTcWrittenReason = null;

    /**
     * @Form\Options({
     *     "label": "Written reason letter sent",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(true)
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"blagle"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $writtenReasonLetterSent = null;

    /**
     * @Form\Options({
     *     "label": "Decision letter sent after written decision date",
     *     "create_empty_option": true,
     *     "render_delimiters": "d m y"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $decisionLetterSentAfterWrittenDecisionDate = null;








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
