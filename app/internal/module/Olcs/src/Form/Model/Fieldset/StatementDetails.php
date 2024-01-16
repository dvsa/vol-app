<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("details")
 * @Form\Options({"label":"Statement Details"})
 */
class StatementDetails extends CaseBase
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Statement type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "statement_type"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $statementType = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"assignedCaseworker","class":"medium"})
     * @Form\Options({
     *     "label": "Assigned caseworker",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "use_groups": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $assignedCaseworker = null;

    /**
     * @Form\Options({"label":"Vehicle registration mark"})
     * @Form\Type("Text")
     */
    public $vrm = null;

    /**
     * @Form\Attributes({"placeholder":""})
     * @Form\Options({"label":"Requestors first name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $requestorsForename = null;

    /**
     * @Form\Attributes({"placeholder":""})
     * @Form\Options({"label":"Requestors last name"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":35})
     */
    public $requestorsFamilyName = null;

    /**
     * @Form\Attributes({"class":"","id":""})
     * @Form\Options({"label":"Requestor body"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":2,"max":40})
     */
    public $requestorsBody = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date stopped",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Validator({
     *      "name": "DateCompare",
     *      "options": {"compare_to":"requestedDate", "compare_to_label":"Date requested", "operator": "lte"}
     * })
     */
    public $stoppedDate = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Date requested",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $requestedDate = null;

    /**
     * @Form\Attributes({"id":"issuedDate"})
     * @Form\Options({
     *     "label": "Issued date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Required(false)
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $issuedDate = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Request mode",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "contact_method"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator("NotEmpty", options={"null"})
     */
    public $contactType = null;

    /**
     * @Form\Attributes({"value":"ct_corr"})
     * @Form\Type("Hidden")
     */
    public $contactDetailsType = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $contactDetailsId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $contactDetailsVersion = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $personId = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $personVersion = null;

    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Authorised decision",
     *     "label_attributes": {
     *         "class": ""
     *     }
     * })
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":5,"max":4000})
     */
    public $authorisersDecision = null;
}
