<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("registered-address")
 * @Form\Type("\Laminas\Form\Fieldset")
 * @Form\Options({"label":"Registered address"})
 * @Form\Attributes({
 *      "class": "address",
 * })
 */
class OperatorRegisteredAddress
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
     * @Form\Attributes({
     *   "class" : "long",
     *   "id" : "",
     *   "data-container-class" : "compound"
     * })
     * @Form\Options({"label":"address_addressLines"})
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
     * @Form\Attributes({
     *   "class" : "long",
     *   "id" : "",
     *   "data-container-class" : "compound"
     * })
     * @Form\Options({"label":"address_addressLine4","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $addressLine4;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"address_townCity","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $town;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({"label":"address_postcode"})
     * @Form\Type("Text")
     * @Form\Filter("Dvsa\Olcs\Transfer\Filter\Postcode")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\Postcode");
     */
    public $postcode;
}
