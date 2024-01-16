<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 * @Form\Options({"label":""})
 */
class PublicInquirySlaMain extends Base
{
    /**
     * @Form\Options({
     *     "label": "Call up letter issued",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "hint": "some hint",
     *     "category": "pi",
     *     "field": "callUpLetterDate"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $callUpLetterDate = null;

    /**
     * @Form\Options({
     *     "label": "Brief sent",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "category": "pi",
     *     "field": "briefToTcDate"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $briefToTcDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Written outcome",
     *     "category": "pi_written_outcome",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $writtenOutcome = null;

    /**
     * @Form\Options({
     *     "label": "Date of written decision",
     *     "create_empty_option": true,
     *     "pattern": "d MMMM y '{{SLA_HINT}}</fieldset>'",
     *     "render_delimiters": true,
     *     "category": "pi",
     *     "field": "tcWrittenDecisionDate"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required": false})
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"piwo_decision"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $tcWrittenDecisionDate = null;

    /**
     * @Form\Options({
     *     "label": "Decision letter sent",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "category": "pi",
     *     "field": "decisionLetterSentDate"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required": false})
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"piwo_decision"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $decisionLetterSentDate = null;

    /**
     * @Form\Options({
     *     "label": "Date of written reason",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "category": "pi",
     *     "field": "tcWrittenReasonDate"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required": false})
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"piwo_reason"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $tcWrittenReasonDate = null;

    /**
     * @Form\Options({
     *     "label": "Written reason letter sent",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "category": "pi",
     *     "field": "writtenReasonLetterDate"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"required": false})
     * @Form\AllowEmpty(true)
     * @Form\Input("\Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({
     *      "name": "ValidateIf",
     *      "options": {
     *          "context_field": "writtenOutcome",
     *          "context_values": {"piwo_reason"},
     *          "allow_empty": true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {"name": "\Common\Form\Elements\Validators\DateNotInFuture"}
     *          }
     *      }
     * })
     */
    public $writtenReasonLetterDate = null;

    /**
     * @Form\Options({
     *     "label": "Written decision letter sent",
     *     "create_empty_option": true,
     *     "render_delimiters": true,
     *     "category": "pi",
     *     "field": "writtenDecisionLetterDate"
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $writtenDecisionLetterDate = null;
}
