<?php


namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 */
class RevocationsSla
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Submission required for approval of ptr?",
     *      "value_options":{
     *          "0":"No",
     *          "1":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "0"})
     */
    public $isSubmissionRequiredForApproval;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date approval submission sent",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $approvalSubmissionIssuedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date approval submission returned",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $approvalSubmissionReturnedDate;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Approval submission TC/TDC",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $approvalSubmissionPresidingTc;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date IOR letter issued",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $iorLetterIssuedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date Response Received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $operatorResponseDueDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date Response Received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $operatorResponseReceivedDate;


    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Is submission required for action?",
     *      "value_options":{
     *          "0":"No",
     *          "1":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({"value": "0"})
     */
    public $isSubmissionRequiredForAction;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date final submission sent",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $finalSubmissionIssuedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date final submission sent",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $finalSubmissionReturnedDate;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Final submission TC/TDC",
     *     "service_name": "Olcs\Service\Data\PresidingTc",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $finalSubmissionPresidingTc;

    //@todo get values from refdata for actions
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Action to be taken",
     *     "value_options":{ "0":"todo get from refdata"},
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $actionToBeTaken;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Revocation letter issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Val
     **/
    public $revocationLetterIssuedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "NFA letter issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $nfaLetterIssuedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Warning letter issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $warningLetterIssuedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "PI Agreed",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $piAgreedDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Other Action agreed",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name":"Date","options":{"format":"Y-m-d"}})
     */
    public $otherActionAgreedDate;
}
