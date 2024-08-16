<?php

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;

/**
 * @codeCoverageIgnore No methods
 */
class PermitRangeDetails
{
    use IdTrait;

    /**
     * @Form\Type("Hidden")
     */
    public $stockId = null;

    /**
     * @Form\Attributes({"id":"journey"})
     * @form\Required(true)
     * @Form\Options({
     *     "label": "Journey type",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "journey"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $journey = null;

    /**
     * @Form\Attributes({"id":"emissionsCategory"})
     * @form\Required(true)
     * @Form\Options({
     *     "label": "Emissions category question",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "emissions_category",
     *     "exclude": {"emissions_cat_na"}
     * })
     * @Form\Type("DynamicSelect")
     */
    public $emissionsCategory = null;

    /**
     * @Form\Name("prefix")
     * @Form\Attributes({"id": "prefix"})
     * @Form\Options({
     *      "label": "Permit Prefix",
     *      "required": "false"
     * })
     * @Form\Type("Text")
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":7})
     * @Form\Required(false)
     */
    public $prefix = null;

    /**
     * @Form\Name("fromNo")
     * @Form\Attributes({"id":"fromNo", "requried": true })
     * @Form\Options({
     *      "label":"Permit Number Range From",
     * })
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Type("Laminas\Form\Element\Number")
     * @Form\Validator("Laminas\Validator\GreaterThan",
     *      options={
     *          "min": "0"
     *      }
     * )
     */
    public $fromNo = null;

    /**
     * @Form\Name("toNo")
     * @Form\Attributes({"id":"toNo", "required": true})
     * @Form\Options({
     *      "label":"Permit Number Range To",
     * })
     * @Form\Validator("Laminas\Validator\Digits")
     * @Form\Type("Laminas\Form\Element\Number")
     * @Form\Validator("Laminas\Validator\GreaterThan",
     *      options={
     *          "min": "0"
     *      }
     * )
     */
    public $toNo = null;

    /**
     * @Form\Name("cabotage")
     * @Form\Attributes({
     *    "id" : "cabotage",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "Cabotage",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "Yes"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     * @Form\Required(false)
     */
    public $cabotage = null;

    /**
     * @Form\Name("ssReserve")
     * @Form\Attributes({
     *    "id" : "reserve",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "Reserve",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "Yes"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     * @Form\Required(false)
     */
    public $ssReserve = null;

    /**
     * @Form\Name("lostReplacement")
     * @Form\Attributes({
     *    "id" : "lostReplacement",
     * })
     * @Form\Options({
     *   "checked_value": "1",
     *   "unchecked_value": "0",
     *   "label": "Lost and Replacement",
     *   "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *   "must_be_value": "Yes"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     * @Form\Required(false)
     */
    public $lostReplacement = null;


    /**
     * @Form\Name("countrys")
     * @Form\Attributes({
     *     "class" : "chosen-select-large",
     *     "id" : "countrys",
     *     "allowWrap":true,
     *     "multiple":"multiple",
     *     "empty": "Select options if applicable",
     *     "data-container-class": "form-control__container",
     * })
     * @Form\Options({
     *     "label": "Restricted Countries",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country",
     *     "required": "false"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $countrys = null;
}
