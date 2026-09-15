<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Annotation;

/**
 * @Form\Name("address")
 * @Form\Type("\Common\Form\Elements\Types\Address")
 * @Form\Attributes({
 *     "class": "address js-postcode-search"
 * })
 */
class Address
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Options({
     *     "label":"Postcode search", "label_attributes": {"class": "form-element__label"}
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Types\PostcodeSearch")
     * @Form\Flags({"priority": 100})
     */
    public $searchPostcode;

    /**
     * @Form\Attributes({
     *   "class" : "long",
     *   "id" : "addressLine1",
     *   "data-container-class" : "compound"
     * })
     * @Form\Options({
     *     "label":"address_addressLines",
     *     "error-message" : "address_addressLine1-error",
     *     "label_attributes": {
     *         "aria-label": "Enter address manually. Address line one"
     *     },
     *     "short-label":"address_addressLine1"
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":90})
     */
    public $addressLine1;

    /**
     * @Form\Attributes({
     *   "class" : "long",
     *   "id" : "",
     *   "data-container-class" : "compound"
     * })
     * @Form\Options({"label":"address_addressLine2","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":90})
     */
    public $addressLine2;

    /**
     * @Form\Attributes({
     *   "class" : "long",
     *   "id" : "",
     *   "data-container-class" : "compound"
     * })
     * @Form\Options({"label":"address_addressLine3","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":100})
     */
    public $addressLine3;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"address_addressLine4","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":35})
     */
    public $addressLine4;

    /**
     * @Form\Attributes({"class":"long","id":"addressTown"})
     * @Form\Options({
     *     "label":"address_townCity",
     *     "short-label":"address_townCity",
     *     "label_attributes": {
     *         "aria-label": "address_townCity"
     *     },
     *     "error-message" : "address_town-error",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":0,"max":30})
     */
    public $town;

    /**
     * @Form\Options({
     *     "label":"address_postcode",
     *     "short-label":"address_postcode",
     * })
     *
     * @Form\Type("Text")
     * @Form\Required(true)
     * @Form\Attributes({"id":"postcode", "required":false})
     * @Form\Validator("Laminas\Validator\NotEmpty", options={"null"})
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\Postcode")
     */
    public $postcode;

    /**
     * @Form\Attributes({"id":"","placeholder":"","value":"GB"})
     * @Form\Options({
     *     "label": "address_country",
     *     "label_attributes": {
     *         "aria-label": "Choose country"
     *     },
     *     "error-message" : "address_country-error",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $countryCode;
}
