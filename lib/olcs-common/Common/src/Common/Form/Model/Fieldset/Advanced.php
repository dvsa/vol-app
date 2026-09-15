<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("advanced")
 * @Form\Options({"label":"Advanced search"})
 */
class Advanced
{
    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({
     *     "label": "Address"
     * })
     * @Form\Type("\Laminas\Form\Element\Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Filter("Laminas\Filter\StringToLower")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":10,"max":100})
     */
    public $address;

    /**
     * @Form\Attributes({"class":"long","id":""})
     * @Form\Options({"label":"Town"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $town;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Case number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $caseNumber;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Transport Manager ID"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $transportManagerId;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Operator ID"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $operatorId;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Vehicle registration mark "})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $vehicleRegMark;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Disk serial number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $diskSerialNumber;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Fabs ref"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $fabsRef;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"Company number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $companyNo;
}
