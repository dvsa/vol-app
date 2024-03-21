<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class RevocationsSla
{
    /**
     * @Form\Type("Radio")
     * @Form\Required(false)
     * @Form\Options({
     *      "label": "Submission required for approval of ptr?",
     *      "value_options":{
     *          "0":"No",
     *          "1":"Yes"
     *      }
     * })
     * @Form\Attributes({"value": "0"})
     */
    public $isSubmissionRequiredForApproval;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date approval submission issued",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ptr-yes"
     *
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $approvalSubmissionIssuedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date approval submission returned",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ptr-yes"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $approvalSubmissionReturnedDate = null;

    /**
     * @Form\Attributes({"id":"approvalSubmissionPresidingTc","class":"__ptr-yes"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Approval submission TC/DTC/TR",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $approvalSubmissionPresidingTc;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date IOR letter issued",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $iorLetterIssuedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date operator's response due",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $operatorResponseDueDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date operator's response received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $operatorResponseReceivedDate = null;


    /**
     * @Form\Type("Radio")
     * @Form\Required(false)
     * @Form\Options({
     *      "label": "Is submission required for action?",
     *      "value_options":{
     *          "0":"No",
     *          "1":"Yes"
     *      }
     * })
     * @Form\Attributes({"value": "0"})
     */
    public $isSubmissionRequiredForAction;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date final submission issued",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__sra-yes"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $finalSubmissionIssuedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Date final submission returned",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__sra-yes"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $finalSubmissionReturnedDate;

    /**
     * @Form\Attributes({"id":"finalSubmissionPresidingTc","class":"__sra-yes"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Final submission TC/DTC/TR",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *      "labelClass":"__sra-yes"
     *
     * })
     * @Form\Type("DynamicSelect")
     */
    public $finalSubmissionPresidingTc;

    /**
     * @Form\Options({
     *     "label": "Action to be taken",
     *     "service_name":"Olcs\Service\Data\ActionToBeTaken",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Required(false)
     * @Form\Type("DynamicSelect")
     */
    public $actionToBeTaken;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Revocation letter issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ior-revoke"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     **/
    public $revocationLetterIssuedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "NFA letter issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ior-nfa"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $nfaLetterIssuedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Warning letter issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ior-warning"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $warningLetterIssuedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "PI agreed",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ior-pi"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $piAgreedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Other action agreed",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass":"__ior-other"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $otherActionAgreedDate = null;
}
