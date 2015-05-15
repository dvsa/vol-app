<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class IrfoPsvAuth extends OrganisationBase
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Service type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a service type",
     *     "service_name": "Olcs\Service\Data\IrfoPsvAuthType"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $irfoPsvAuthType = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Validity period",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *      "value_options":{
     *          "1":"1 year",
     *          "2":"2 years",
     *          "3":"3 years",
     *          "4":"4 years",
     *          "5":"5 years"
     *      },
     * })
     * @Form\Type("Zend\Form\Element\Select")
     */
    public $validityPeriod = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Status",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a status",
     *     "category": "irfo_auth_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"", "required": false})
     * @Form\Options({
     *     "label": "IRFO file number",
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $irfoFileNo = null;

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
     * @Form\AllowEmpty(true)
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "ValidateIf",
     *      "options":{
     *          "context_field": "inForceDate",
     *          "context_values": {"--"},
     *          "context_truth": false,
     *          "allow_empty" : true,
     *          "validators": {
     *              {"name": "Date", "options": {"format": "Y-m-d"}},
     *              {
     *                  "name": "DateCompare",
     *                  "options": {
     *                      "compare_to":"inForceDate",
     *                      "compare_to_label":"In force date",
     *                      "operator": "gt",
     *                  }
     *              }
     *          }
     *      }
     * })
     *
     */
    public $expiryDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Application sent date",
     *     "create_empty_option": true,
     *     "render_delimiters": false,
     *     "max_year_delta": "+1",
     *     "min_year_delta": "-40",
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     */
    public $applicationSentDate;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({"label":"Service route from"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":30}})
     */
    public $serviceRouteFrom = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({"label":"Service route to"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"min":1,"max":30}})
     */
    public $serviceRouteTo = null;

    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "Journey frequency",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select a journey frequency",
     *     "category": "irfo_psv_journey_freq"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $journeyFrequency = null;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Application fee exempt"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isFeeExemptApplication;

    /**
     * @Form\Options({"checked_value":"Y","unchecked_value":"N","label":"Annual fee exempt"})
     * @Form\Type("OlcsCheckbox")
     */
    public $isFeeExemptAnnual;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"class":"extra-long","id":"exemptionDetails", "required":false})
     * @Form\Options({"label":"Exemption reason"})
     * @Form\Type("TextArea")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":255}})
     */
    public $exemptionDetails;
}
