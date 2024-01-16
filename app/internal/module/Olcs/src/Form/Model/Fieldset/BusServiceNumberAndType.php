<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-number-and-type")
 */
class BusServiceNumberAndType extends BusRegDetails
{
    /**
     * @Form\Attributes({"class":"","id":"serviceNo"})
     * @Form\Options({"label":"Service number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1,"max":70})
     */
    public $serviceNo = null;

    /**
     * @Form\Attributes({"class":"add-another"})
     * @Form\ComposedObject({
     *     "target_object":"Olcs\Form\Model\Fieldset\BusReg\OtherServices",
     *     "is_collection":true,
     *     "options":{
     *         "count":1,
     *         "label":"Other Service numbers",
     *          "hint":"markup-other-service-numbers-hint",
     *          "hint_at_bottom":true
     *     }
     * })
     */
    public $otherServices = null;

    /**
     * @Form\Attributes({"class":"medium","id":"startPoint"})
     * @Form\Options({"label":"Start point"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1,"max":100})
     */
    public $startPoint = null;

    /**
     * @Form\Attributes({"class":"medium","id":"finishPoint"})
     * @Form\Options({"label":"Finish point"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1,"max":100})
     */
    public $finishPoint = null;

    /**
     * @Form\Attributes({"class":"medium","id":"via"})
     * @Form\Options({"label":"Via"})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1,"max":255})
     */
    public $via = null;

    /**
     * @Form\Attributes({
     *     "id":"busServiceTypes",
     *     "placeholder":"",
     *     "class":"chosen-select-medium",
     *     "multiple":"multiple",
     * })
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Service type",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\BusServiceType",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Filter({"name":"Common\Filter\NullToArray"})
     */
    public $busServiceTypes = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":"otherDetails"})
     * @Form\Options({"label":"Other N&P details"})
     * @Form\Required(false)
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":800})
     */
    public $otherDetails = null;

    /**
     * @Form\Attributes({"id":"receivedDate"})
     * @Form\Options({
     *     "label": "Date received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator("Common\Form\Elements\Validators\DateNotInFuture")
     */
    public $receivedDate = null;

    /**
     * @Form\Attributes({"id":"effectiveDate"})
     * @Form\Options({
     *     "label": "Effective date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $effectiveDate = null;

    /**
     * @Form\Attributes({"id":"endDate"})
     * @Form\Options({
     *     "label": "End date",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $endDate = null;

    /**
     * @Form\Attributes({"id":"busNoticePeriod","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Rules",
     *     "service_name": "Olcs\Service\Data\BusNoticePeriod",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("DynamicSelect")
     */
    public $busNoticePeriod = null;
}
