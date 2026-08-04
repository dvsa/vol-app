<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("address")
 * @Form\Type("\Common\Form\Elements\Types\Address")
 * @Form\Attributes({
 *     "class": "address js-postcode-search",
 *     "aria-live":"assertive"
 * })
 */
class AddressOptional
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
     *     "label":"Postcode search", "label_attributes": {"class": "form-element__label"},
     * })
     * @Form\Required(false)
     * @Form\Type("Common\Form\Elements\Types\PostcodeSearch")
     * @Form\Flags({"priority": 100})
     */
    public $searchPostcode;

    /**
     * @Form\Attributes({
     *   "class": "long", "id": "", "data-container-class": "compound"
     * })
     * @Form\Options({
     *     "label":"address_addressLines",
     *     "label_attributes": {
     *         "aria-label": "Enter address manually. Establishment address line 1"
     *     },
     *     "short-label":"address_addressLine1"
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
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
     */
    public $addressLine3;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"address_addressLine4","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $addressLine4;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({
     *    "label":"address_townCity",
     *    "label_attributes": {
     *        "aria-label": "Establishment town/city"
     *    }
     * })
     * @Form\Required(false)
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $town;

    /**
     * @Form\Options({
     *     "label":"address_postcode",
     *     "label_attributes": {
     *         "aria-label": "Establishment postcode"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Attributes({"id":"postcodeOptional", "required":false})
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\Postcode",
     *      options={"allow_empty": true}
     * );
     */
    public $postcode;

    /**
     * @Form\Attributes({"id":"","placeholder":"","value":"GB"})
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "address_country",
     *     "label_attributes": {
     *         "aria-label": "Choose establishment country"
     *     },
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\Country"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $countryCode;
}
