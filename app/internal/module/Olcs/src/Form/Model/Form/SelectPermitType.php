<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("selectPermitType")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class SelectPermitType
{
    /**
     * @Form\Attributes({"id":"permitType"})
     * @Form\Options({
     *     "empty_option": "Please Select",
     *     "label": "Select Permit Type",
     *     "short-label": "Permit Type",
     *     "label_attributes": {"id": "label-permit-type"},
     *     "service_name": "Common\Service\Data\IrhpPermitType",
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Validator("Laminas\Validator\NotEmpty")
     */
    public $permitType = null;

    /**
     * @Form\Name("year")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "data-container-class":"js-hidden yearSelect",
     *   "id" : "yearList",
     * })
     * @Form\Options({
     *      "label": "Select a year",
     *      "fieldset-attributes": {"id": "year-list", "class":"inline"},
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "disable_inarray_validator" : true,
     * })
     * @Form\Type("Select")
     */
    public $year = null;

    /**
     * @Form\Name("stock")
     * @Form\Required(false)
     * @Form\Attributes({
     *   "data-container-class":"js-hidden stock",
     *   "id" : "stock",
     * })
     * @Form\Options({
     *      "label": "Select a stock",
     *      "fieldset-attributes": {"id": "year-list", "class":"inline"},
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "disable_inarray_validator" : true,
     * })
     * @Form\Type("Select")
     */
    public $stock = null;

    /**
     * @Form\Name("bilateralCountries")
     * @Form\Attributes({
     *   "value":"<div id=""bilateralCountries""></div>",
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $bilateralCountries = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\ContinueCancelFormActions")
     */
    public $formActions = null;
}
