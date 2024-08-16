<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("registered-address")
 * @Form\Type("\Laminas\Form\Fieldset")
 * @Form\Options({"label":"Registered address"})
 * @Form\Attributes({
 *      "class": "address",
 * })
 */
class UnlicensedOperatorAddress
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Address lines"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     */
    public $addressLine1 = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Address line 2","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     */
    public $addressLine2 = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Address line 3","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     */
    public $addressLine3 = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Address line 4","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     */
    public $addressLine4 = null;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Town/City","label_attributes":{"class":"govuk-visually-hidden"}})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Laminas\Filter\StringTrim"})
     */
    public $town = null;

    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({"label":"Postcode"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Dvsa\Olcs\Transfer\Filter\Postcode"})
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\Postcode");
     */
    public $postcode = null;
}
