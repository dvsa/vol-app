<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("inspection-request-details")
 */
class InspectionRequestDetails
{
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

    /**
     * @Form\Options({
     *     "label": "Report type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "insp_report_type"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":"", "required":false})
     * @Form\Type("DynamicSelect")
     */
    public $reportType = null;

    /**
     * @Form\Attributes({"id":"operatingCentre","placeholder":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Operating centre",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\OperatingCentresForInspectionRequest",
     *     "create_empty_option": false,
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter("Common\Filter\NullToArray")
     */
    public $operatingCentre = null;

    /**
     * @Form\Attributes({"class":"","id":"inspectorName"})
     * @Form\Options({"label":"Inspector name"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $inspectorName = null;

    /**
     * @Form\Options({
     *     "label": "Request type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "insp_request_type"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":"", "required":false})
     * @Form\Type("DynamicSelect")
     */
    public $requestType = null;

    /**
     * @Form\Attributes({"id":"requestDate"})
     * @Form\Options({
     *     "label": "Request date",
     *     "render_delimiters": false
     * })
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator("Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $requestDate = null;

    /**
     * @Form\Attributes({"id":"dueDate"})
     * @Form\Options({
     *     "label": "Due date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(true)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "Common\Form\Elements\Validators\InspectionRequestDueDate"})
     */
    public $dueDate = null;

    /**
     * @Form\Attributes({"id":"returnDate"})
     * @Form\Options({
     *     "label": "Return date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     */
    public $returnDate = null;

    /**
     * @Form\Options({
     *     "label": "Result",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "insp_result_type"
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":"", "required":false})
     * @Form\Type("DynamicSelect")
     */
    public $resultType = null;

    /**
     * @Form\Attributes({"id":"fromDate"})
     * @Form\Options({
     *     "label": "Investigation from",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     */
    public $fromDate = null;

    /**
     * @Form\Attributes({"id":"fromDate"})
     * @Form\Options({
     *     "label": "Investigation to",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     */
    public $toDate = null;

    /**
     * @Form\Attributes({"class":"","id":"vehiclesExaminedNo"})
     * @Form\Options({"label":"Vehicles"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $vehiclesExaminedNo = null;

    /**
     * @Form\Attributes({"class":"","id":"trailersExaminedNo"})
     * @Form\Options({"label":"Trailers"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $trailersExaminedNo = null;

    /**
     * @Form\Attributes({"class":"long","id":"requestorNotes"})
     * @Form\Options({"label":"Caseworker comments"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $requestorNotes = null;

    /**
     * @Form\Attributes({"class":"long","id":"inspectorNotes"})
     * @Form\Options({"label":"Examiner comments"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $inspectorNotes = null;
}
