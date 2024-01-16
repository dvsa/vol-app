<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class PermitStockDetails
{
    use IdTrait;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("irhpPermitType")
     * @Form\Attributes({"id":"irhpPermitType","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Permit Type",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Common\Service\Data\IrhpPermitType",
     *     "required": true
     * })
     */
    public $irhpPermitType = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("country")
     * @Form\Attributes({"id":"country","placeholder":"","class":"medium", "data-container-class":"stockCountry js-hidden"})
     * @Form\Options({
     *     "label": "Country",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country",
     *     "category": "isPermitState",
     *     "empty_option": "Please Select",
     * })
     * @Form\Required(false)
     */
    public $country = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("permitCategory")
     * @Form\Required(false)
     * @Form\Attributes({"id":"permitCategory","placeholder":"","class":"medium", "data-container-class":"permitCategoryFields js-hidden"})
     * @Form\Options({
     *     "label": "Permit Category",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Common\Service\Data\RefData",
     *     "context": "permit_cat",
     *     "required": false
     * })
     */
    public $permitCategory = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("applicationPathGroup")
     * @Form\Required(false)
     * @Form\Attributes({"id":"applicationPathGroup","placeholder":"","class":"medium", "data-container-class":"pathProcess js-hidden"})
     * @Form\Options({
     *     "label": "Application Path",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "service_name": "Common\Service\Data\ApplicationPathGroup",
     *     "required": false
     * })
     */
    public $applicationPathGroup = null;

    /**
     * @Form\Name("applicationPathGroupHtml")
     * @Form\Attributes({"id":"applicationPathGroupHtml", "data-container-class":"pathProcess js-hidden"})
     * @Form\Options({
     *     "label": "Application Path"
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $applicationPathGroupHtml = null;

    /**
     * @Form\Type("DynamicSelect")
     * @Form\Name("businessProcess")
     * @Form\Attributes({"id":"businessProcess","placeholder":"","class":"medium"})
     * @Form\Options({
     *     "label": "Business Process",
     *     "disable_inarray_validator": false,
     *     "empty_option": "Please Select",
     *     "category": "app_business_process",
     *     "required": true
     * })
     */
    public $businessProcess = null;

    /**
     * @Form\Name("businessProcessHtml")
     * @Form\Options({
     *     "label": "Business Process"
     * })
     * @Form\Type("Common\Form\Elements\Types\Html")
     */
    public $businessProcessHtml = null;

    /**
     * @Form\Name("periodNameKey")
     * @Form\Attributes({"id": "periodNameKey"})
     * @Form\Options({
     *      "label": "Period selection translation key "
     * })
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":512})
     * @Form\Type("Text")
     * @Form\Required(false)
     */
    public $periodNameKey = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"validFrom","placeholder":"","class":"medium", "data-container-class":"stockDates"})
     * @Form\Options({
     *     "label": "Validity Period Start",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $validFrom = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"validTo","placeholder":"","class":"medium", "data-container-class":"stockDates"})
     * @Form\Options({
     *     "label": "Validity Period End",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter({"name":"DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     */
    public $validTo = null;

    /**
     * @Form\Name("initialStock")
     * @Form\Attributes({"id": "initialStock"})
     * @Form\Options({
     *      "label": "Quota"
     * })
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Type("Laminas\Form\Element\Number")
     * @Transfer\Validator({
     *      "name":"Laminas\Validator\Between",
     *      "options": {
     *          "min": -1,
     *          "max": 9999999
     *      }
     * })
     * @Form\Required(false)
     */
    public $initialStock = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({
     *   "class" : "input--hiddenss",
     *   "id" : "hiddenss",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "Hidden from Self Serve?",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $hiddenSs = null;
}
