<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class IrfoGvPermit extends OrganisationBase
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Permit type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
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
     * @Form\Validator("Digits")
     */
    public $yearRequired = null;

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
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $inForceDate;

    /**
     * @Form\Attributes({"id":"expiryDate"})
     * @Form\Options({
     *     "label": "Expiry date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+2",
     *     "min_year_delta": "-40",
     *     "hint": "The calculated expiry date is <span id=calculatedExpiryDateText>dd/mm/yyyy</span>",
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "expiryDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "\Common\Validator\Date"},
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"inForceDate",
     *                      "compare_to_label":"In force date",
     *                      "operator": "gte",
     *                  }
     *              }
     *          }
     *      }
     * })
     */
    public $expiryDate;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Fee exempt"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isFeeExempt;

    /**
     * @Form\Required(true)
     * @Form\Type("TextArea")
     * @Form\Options({"label":"Exemption reason"})
     * @Form\Attributes({"class":"extra-long","id":"exemptionDetails", "required":false})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "isFeeExempt",
     *          "context_values": {"Y"},
     *          "allow_empty": false,
     *          "validators": {
     *              {"name":"Laminas\Validator\NotEmpty"},
     *              {"name":"Laminas\Validator\StringLength","options":{"max":255}}
     *          }
     *      }
     * })
     */
    public $exemptionDetails;

    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "No of copies required"})
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     */
    public $noOfCopies = null;
}
