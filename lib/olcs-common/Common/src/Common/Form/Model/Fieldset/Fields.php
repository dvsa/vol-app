<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("fields")
 * @Form\Options({})
 */
class Fields
{
    /**
     * @Form\Attributes({"id":"","class":"extra-long"})
     * @Form\Options({"label": "Case summary"})
     * @Form\Type("\Laminas\Form\Element\Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Filter("Laminas\Filter\StringToLower")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":10,"max":100})
     */
    public $description;

    /**
     * @Form\Attributes({"class":"medium","id":""})
     * @Form\Options({"label":"ECMS number"})
     * @Form\Required(false)
     * @Form\Type("Text")
     */
    public $ecmsNo;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $licence;

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
}
