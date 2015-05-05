<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class IrfoGvPermit extends OrganisationBase
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"idHtml", "required": false})
     * @Form\Options({
     *     "label": "Permit No",
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $idHtml = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Permit type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a permit type",
     *     "service_name": "Olcs\Service\Data\IrfoGvPermitType"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irfoGvPermitType = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "Year",
     *     "empty_option": "Please Select",
     *     "max_year_delta": "+3",
     *     "min_year_delta": "-40",
     * })
     * @Form\Type("YearSelect")
     * @Form\Validator({"name":"Digits"})
     */
    public $yearRequired = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a status",
     *     "category": "irfo_permit_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irfoPermitStatus = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"createdOnHtml", "required": false})
     * @Form\Options({
     *     "label": "Create date",
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $createdOnHtml = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "label": "In force date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+1",
     *     "min_year_delta": "-40",
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $inForceDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Expiry date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+3",
     *     "min_year_delta": "-40",
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $expiryDate;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Fee exempt"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isFeeExempt;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"class":"extra-long","id":"exemptionDetails", "required":false})
     * @Form\Options({"label":"Exemption reason"})
     * @Form\AllowEmpty(true)
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isFeeExempt",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"Zend\Validator\NotEmpty"},
     *              {"name":"Zend\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $exemptionDetails;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "No of copies required"})
     * @Form\Type("Text")
     * @Form\Validator({"name":"Digits"})
     */
    public $noOfCopies = null;
}
