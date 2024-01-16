<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"label":"scanning.details"})
 */
class ScanningDetails
{
    /**
     * @Form\Attributes({"id":"category","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.category",
     *     "service_name": "Olcs\Service\Data\ScannerCategory",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"subCategory","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.sub_category",
     *     "service_name": "Olcs\Service\Data\ScannerSubCategory",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $subCategory = null;

    /**
     * @Form\Attributes({"id":"description","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.description",
     *     "service_name": "Olcs\Service\Data\SubCategoryDescription",
     *     "context": {}
     * })
     * @Form\Type("DynamicSelect")
     */
    public $description = null;

    /**
     * @Form\Attributes({"id":"other_description","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.description"
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name": "Laminas\Validator\NotEmpty"})
     */
    public $otherDescription = null;

    /**
     * @Form\Attributes({"id":"entity_identifier","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.entity"
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name": "Laminas\Validator\NotEmpty"})
     */
    public $entityIdentifier = null;

    /**
     * @Form\Attributes({"id" : "back_scan"})
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "scanning.data.back_scan",
     *   "must_be_value": "Yes"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     * @Form\Required(false)
     */
    public $backScan = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"received_date"})
     * @Form\Options({
     *     "label": "scanning.data.received_date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "fieldsetClass": "received_date"
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $dateReceived = null;
}
